const fs = require('fs');
const path = require('path');

const DB_FILE = path.join(__dirname, 'db.json');
const db = JSON.parse(fs.readFileSync(DB_FILE, 'utf8'));

// Simulating start_app.js render logic
function parseViewLoops(content, ctx) {
    if (content.includes('<?php foreach ($feed as $item):')) {
        const loopRegex = /<\?php\s+foreach\s*\(\$feed\s+as\s+\$item\):\s*[\s\S]*?\?>([\s\S]*?)<\?php\s+endforeach;\s*\?>/;
        const match = content.match(loopRegex);
        if (match) {
            let compiledFeed = '';
            (ctx.feed || []).forEach(item => {
                let block = match[1];
                const age = item.date_of_birth ? (new Date().getFullYear() - new Date(item.date_of_birth).getFullYear()) : 25;
                const photos = JSON.parse(item.photos || '[]');
                const displayPhoto = photos.length > 0 ? photos[0] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80';
                const genderDisp = item.gender_identity === 'other' ? (item.gender_custom || 'Other') : item.gender_identity;
                
                block = block.replace(/<\?=\s*htmlspecialchars\(\$item\['name'\]\)\s*\?>/g, item.name || 'Anonymous');
                block = block.replace(/<\?=\s*date_diff[\s\S]*?\?>/g, age);
                block = block.replace(/<\?=\s*htmlspecialchars\(\$item\['pronouns'\]\s*(?:\?\?|:\?)\s*'[^']+'\)\s*\?>/g, item.pronouns || 'they/them');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$item\['sexual_orientation'\]\s*(?:\?\?|:\?)\s*'[^']+'\)\s*\?>/g, item.sexual_orientation || 'queer');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$item\['gender_identity'\]\s*===\s*'other'[\s\S]*?\$item\['gender_identity'\]\)\s*\?>/g, genderDisp);
                block = block.replace(/<\?=\s*htmlspecialchars\(\$item\['city'\]\s*(?:\?\?|:\?)\s*'[^']+'\)\s*\?>/g, item.city || 'San Francisco');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$item\['country'\]\s*(?:\?\?|:\?)\s*'[^']+'\)\s*\?>/g, item.country || 'USA');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$item\['headline'\]\s*(?:\?\?|:\?)\s*'[^']+'\)\s*\?>/g, item.headline || 'Match');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$displayPhoto\)\s*\?>/g, displayPhoto);
                block = block.replace(/<\?=\s*\$item\['user_id'\]\s*\?>/g, item.user_id);
                
                compiledFeed += block;
            });
            content = content.replace(loopRegex, compiledFeed);
        }
    }
    return content;
}

function resolveExpression(expr, ctx) {
    expr = expr.trim();
    const htmlSpecMatch = expr.match(/^htmlspecialchars\((.*)\)$/);
    if (htmlSpecMatch) {
        expr = htmlSpecMatch[1].trim();
    }
    if (expr === "currentUserId" || expr === "$currentUser['id']") return ctx.currentUser ? ctx.currentUser.id : '';
    if (expr === "$currentUser['name']") return ctx.currentUser ? ctx.currentUser.name : '';
    if (expr === "$currentUser['email']") return ctx.currentUser ? ctx.currentUser.email : '';
    if (expr === "$currentUser['role'] ?? ''" || expr === "$currentUser['role']") return ctx.currentUser ? (ctx.currentUser.role || 'user') : '';
    if (expr === "$currentUser['tier'] ?? 'free'" || expr === "$currentUser['tier']") return ctx.currentUser ? (ctx.currentUser.tier || 'free') : 'free';
    if (expr === "$token" || expr === "$_COOKIE['jwt_token'] ?? ''") return ctx.token || '';
    if (expr === "$recipientId") return ctx.recipientId || '0';
    if (expr === "$viewTargetId") return ctx.viewTargetId || '0';
    return '';
}

function evaluateCondition(cond, ctx) {
    cond = cond.trim();
    if (cond === '$currentUser') {
        return ctx.currentUser !== null;
    }
    if (cond.startsWith('empty(')) {
        const match = cond.match(/empty\(\$([^)]+)\)/);
        if (match) {
            const varName = match[1];
            const arr = ctx[varName];
            return !arr || arr.length === 0;
        }
    }
    if (cond.includes('role') && cond.includes('admin')) {
        return ctx.currentUser && ctx.currentUser.role === 'admin';
    }
    if (cond.includes('tier') && cond.includes('premium')) {
        return ctx.currentUser && ctx.currentUser.tier === 'premium';
    }
    if (cond === '$isPremium') {
        return ctx.currentUser && ctx.currentUser.tier === 'premium';
    }
    return true;
}

function compilePhpTemplate(html, ctx) {
    let output = '';
    let pos = 0;
    const stack = [];
    
    const isRendering = () => stack.every(v => v);

    while (pos < html.length) {
        const startTag = html.indexOf('<?', pos);
        if (startTag === -1) {
            if (isRendering()) output += html.substring(pos);
            break;
        }

        if (isRendering()) {
            output += html.substring(pos, startTag);
        }

        const endTag = html.indexOf('?>', startTag);
        if (endTag === -1) {
            break;
        }

        const tagContent = html.substring(startTag + 2, endTag).trim();
        pos = endTag + 2;

        if (tagContent.startsWith('=')) {
            if (isRendering()) {
                const expr = tagContent.substring(1).trim();
                output += resolveExpression(expr, ctx);
            }
        } else if (tagContent.startsWith('php')) {
            const phpCode = tagContent.substring(3).trim();
            if (phpCode.startsWith('if')) {
                // Find matching expression up to colon and end of block
                const condMatch = phpCode.match(/if\s*\(([\s\S]*)\):$/);
                if (condMatch) {
                    const condStr = condMatch[1].trim();
                    const res = evaluateCondition(condStr, ctx);
                    stack.push(res);
                    console.log(`IF Condition: [${condStr}] => Evaluated: ${res}. Stack now: [${stack.join(', ')}]`);
                }
            } else if (phpCode.startsWith('else:')) {
                if (stack.length > 0) {
                    const last = stack.pop();
                    stack.push(!last);
                    console.log(`ELSE: Inverted last value to: ${!last}. Stack now: [${stack.join(', ')}]`);
                }
            } else if (phpCode.startsWith('endif;')) {
                const popped = stack.pop();
                console.log(`ENDIF: Popped: ${popped}. Stack now: [${stack.join(', ')}]`);
            }
        }
    }
    return output;
}

const browsePath = path.join(__dirname, '..', 'frontend', 'views', 'browse.php');
let template = fs.readFileSync(browsePath, 'utf8');

// Inline header & footer BEFORE parsing using flexible patterns
const headerPath = path.join(__dirname, '..', 'frontend', 'views', 'header.php');
const footerPath = path.join(__dirname, '..', 'frontend', 'views', 'footer.php');
const headerHtml = fs.readFileSync(headerPath, 'utf8');
const footerHtml = fs.readFileSync(footerPath, 'utf8');

template = template.replace(/include\s+__DIR__\s*\.\s*['"]\/header\.php['"];?/g, `?>${headerHtml}<?php`);
template = template.replace(/include\s+__DIR__\s*\.\s*['"]\/footer\.php['"];?/g, `?>${footerHtml}<?php`);

const loggedUser = { id: 1, name: "Admin", tier: "premium", role: "admin" };
const matches = db.profiles.filter(p => p.user_id !== loggedUser.id);

const loopedTemplate = parseViewLoops(template, { feed: matches, currentUser: loggedUser });
const compiledOutput = compilePhpTemplate(loopedTemplate, { feed: matches, currentUser: loggedUser, token: "mock_token" });

console.log("Compiled Output size:", compiledOutput.length);
console.log("Does compiled output contain 'Looking for connections...'?", compiledOutput.includes("Looking for connections..."));
console.log("Does compiled output contain 'Chris Lopez'?", compiledOutput.includes("Chris Lopez"));
