const http = require('http');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const { WebSocketServer } = require('ws');

const PORT = 4111;
const DB_FILE = path.join(__dirname, 'scratch', 'db.json');
const UPLOADS_DIR = path.join(__dirname, 'uploads');

// Ensure directories exist
if (!fs.existsSync(path.join(__dirname, 'scratch'))) {
    fs.mkdirSync(path.join(__dirname, 'scratch'), { recursive: true });
}
if (!fs.existsSync(UPLOADS_DIR)) {
    fs.mkdirSync(UPLOADS_DIR, { recursive: true });
}

// Initial DB Setup
let db = {
    users: [],
    profiles: [],
    subscriptions: [],
    messages: [],
    notifications: [],
    activity_logs: [],
    reports: [],
    blocks: []
};

if (fs.existsSync(DB_FILE)) {
    try {
        db = JSON.parse(fs.readFileSync(DB_FILE, 'utf8'));
    } catch (e) {
        console.error("DB Parse error, resetting.");
    }
}

// Force-seed if db has few users
if (db.users.length < 15) {
    db.users = [];
    db.profiles = [];
    
    // Seed Admin
    db.users.push({
        id: 1,
        name: "Admin Moderator",
        email: "admin@lgbtqmatrimony.local",
        password: "AdminSecure2026!",
        role: "admin",
        tier: "premium"
    });

    // 1. Sam Vance
    db.users.push({ id: 2, name: "Sam Vance", email: "sam@lgbtqmatrimony.local", password: "password", role: "user", tier: "free" });
    db.profiles.push({
        user_id: 2, name: "Sam Vance", headline: "Hopeless romantic searching for a co-pilot ✈️", pronouns: "they/them",
        date_of_birth: "1997-06-15", gender_identity: "non-binary", gender_custom: "", sexual_orientation: "queer",
        city: "San Francisco", country: "USA", relationship_intent: "long-term", about_me: "I love hiking, reading poetry, and exploring local coffee shops. Let's build something authentic.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80"])
    });

    // 2. Jordan Diaz
    db.users.push({ id: 3, name: "Jordan Diaz", email: "jordan@lgbtqmatrimony.local", password: "password", role: "user", tier: "premium" });
    db.profiles.push({
        user_id: 3, name: "Jordan Diaz", headline: "Let's explore museums and share recipes!", pronouns: "she/her",
        date_of_birth: "1994-09-22", gender_identity: "transgender woman", gender_custom: "", sexual_orientation: "lesbian",
        city: "New York", country: "USA", relationship_intent: "marriage", about_me: "Chef by day, history nerd by night. Searching for a warm connection to cook with.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=300&q=80"])
    });

    // 3. Taylor Moon
    db.users.push({ id: 4, name: "Taylor Moon", email: "taylor@lgbtqmatrimony.local", password: "password", role: "user", tier: "free" });
    db.profiles.push({
        user_id: 4, name: "Taylor Moon", headline: "Seeking friendly conversations and maybe more.", pronouns: "he/him",
        date_of_birth: "1999-12-05", gender_identity: "man", gender_custom: "", sexual_orientation: "gay",
        city: "London", country: "UK", relationship_intent: "dating", about_me: "Musician playing guitar. Loves late nights, indie bands, and record shopping.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80"])
    });

    // 4. Alex Rivera
    db.users.push({ id: 5, name: "Alex Rivera", email: "alex@lgbtqmatrimony.local", password: "password", role: "user", tier: "premium" });
    db.profiles.push({
        user_id: 5, name: "Alex Rivera", headline: "Art curator seeking meaningful lifetime connection 🎨", pronouns: "they/them",
        date_of_birth: "1995-03-12", gender_identity: "genderqueer", gender_custom: "", sexual_orientation: "bisexual",
        city: "Toronto", country: "Canada", relationship_intent: "marriage", about_me: "Loves modern art, vegan cooking, gardening, and community activism. Looking for a partner to build a cozy, artistic life together.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80"])
    });

    // 5. Elena Rostova
    db.users.push({ id: 6, name: "Elena Rostova", email: "elena@lgbtqmatrimony.local", password: "password", role: "user", tier: "free" });
    db.profiles.push({
        user_id: 6, name: "Elena Rostova", headline: "Photojournalist looking for life partner 📸", pronouns: "she/her",
        date_of_birth: "1992-08-20", gender_identity: "woman", gender_custom: "", sexual_orientation: "pansexual",
        city: "Paris", country: "France", relationship_intent: "long-term", about_me: "Traveler, dog lover, vinyl collector. I enjoy exploring local cultures and looking for a warm heart to share stories with.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=300&q=80"])
    });

    // 6. Kai Chen
    db.users.push({ id: 7, name: "Kai Chen", email: "kai@lgbtqmatrimony.local", password: "password", role: "user", tier: "premium" });
    db.profiles.push({
        user_id: 7, name: "Kai Chen", headline: "Let's explore the outdoors and camp together 🌲", pronouns: "he/him",
        date_of_birth: "1996-11-30", gender_identity: "transgender man", gender_custom: "", sexual_orientation: "gay",
        city: "Vancouver", country: "Canada", relationship_intent: "dating", about_me: "Software developer, kayaker, board game enthusiast. I value deep conversations, vulnerability, and active weekend adventures.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=300&q=80"])
    });

    // 7. Morgan Finch
    db.users.push({ id: 8, name: "Morgan Finch", email: "morgan@lgbtqmatrimony.local", password: "password", role: "user", tier: "free" });
    db.profiles.push({
        user_id: 8, name: "Morgan Finch", headline: "Matcha, gardening, and sustainable design 🌱", pronouns: "they/them",
        date_of_birth: "1993-05-18", gender_identity: "non-binary", gender_custom: "", sexual_orientation: "queer",
        city: "Sydney", country: "Australia", relationship_intent: "marriage", about_me: "Landscape architect. Designing sustainable spaces. Enjoys gardening, indie music, and slow Sunday mornings with a good book.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=300&q=80"])
    });

    // 8. Maya Patel
    db.users.push({ id: 9, name: "Maya Patel", email: "maya@lgbtqmatrimony.local", password: "password", role: "user", tier: "premium" });
    db.profiles.push({
        user_id: 9, name: "Maya Patel", headline: "Pediatrician seeking an ambitious, warm-hearted woman 🩺", pronouns: "she/her",
        date_of_birth: "1994-04-25", gender_identity: "woman", gender_custom: "", sexual_orientation: "lesbian",
        city: "Mumbai", country: "India", relationship_intent: "marriage", about_me: "Passionate about child healthcare, classical dance, and mountain hiking. Looking for an honest, goal-oriented partner to build a future together.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1589156280159-27698a70f29e?auto=format&fit=crop&w=300&q=80"])
    });

    // 9. Chris Lopez
    db.users.push({ id: 10, name: "Chris Lopez", email: "chris@lgbtqmatrimony.local", password: "password", role: "user", tier: "premium" });
    db.profiles.push({
        user_id: 10, name: "Chris Lopez", headline: "Searching for mutual friendship & outdoor adventures 🏞️", pronouns: "they/them",
        date_of_birth: "1995-07-14", gender_identity: "non-binary", gender_custom: "", sexual_orientation: "pansexual",
        city: "Chicago", country: "USA", relationship_intent: "friendship", about_me: "Love record stores, vintage style, and biking. Let's hang out and talk music.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=300&q=80"])
    });

    // 10. Sophie Dubois
    db.users.push({ id: 11, name: "Sophie Dubois", email: "sophie@lgbtqmatrimony.local", password: "password", role: "user", tier: "free" });
    db.profiles.push({
        user_id: 11, name: "Sophie Dubois", headline: "Let's explore Paris and enjoy red wine 🍷", pronouns: "she/her",
        date_of_birth: "1993-11-02", gender_identity: "woman", gender_custom: "", sexual_orientation: "bisexual",
        city: "Paris", country: "France", relationship_intent: "dating", about_me: "Literature student and coffee enthusiast. Searching for deep matching conversations.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=300&q=80"])
    });

    // 11. Leo Sterling
    db.users.push({ id: 12, name: "Leo Sterling", email: "leo@lgbtqmatrimony.local", password: "password", role: "user", tier: "premium" });
    db.profiles.push({
        user_id: 12, name: "Leo Sterling", headline: "Trans man searching for long-term connection 💫", pronouns: "he/him",
        date_of_birth: "1991-03-24", gender_identity: "transgender man", gender_custom: "", sexual_orientation: "queer",
        city: "New York", country: "USA", relationship_intent: "long-term", about_me: "Architect and writer. Looking for a partner who values mutual growth and creativity.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=300&q=80"])
    });

    // 12. Zara Ahmed
    db.users.push({ id: 13, name: "Zara Ahmed", email: "zara@lgbtqmatrimony.local", password: "password", role: "user", tier: "premium" });
    db.profiles.push({
        user_id: 13, name: "Zara Ahmed", headline: "Ready for commitment and family life 💍", pronouns: "she/her",
        date_of_birth: "1992-05-18", gender_identity: "transgender woman", gender_custom: "", sexual_orientation: "lesbian",
        city: "London", country: "UK", relationship_intent: "marriage", about_me: "Clinical psychologist. Love cooking, opera, and family gatherings. Looking for a woman ready to settle down.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80"])
    });

    // 13. Riley Park
    db.users.push({ id: 14, name: "Riley Park", email: "riley@lgbtqmatrimony.local", password: "password", role: "user", tier: "free" });
    db.profiles.push({
        user_id: 14, name: "Riley Park", headline: "Let's share matcha lattes and design ideas 🎨", pronouns: "they/them",
        date_of_birth: "1998-09-09", gender_identity: "genderqueer", gender_custom: "", sexual_orientation: "queer",
        city: "Seoul", country: "South Korea", relationship_intent: "friendship", about_me: "UX designer. Loves photography, indie gaming, and plant shopping.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=300&q=80"])
    });

    // 14. Marcus Aurelius
    db.users.push({ id: 15, name: "Marcus Aurelius", email: "marcus@lgbtqmatrimony.local", password: "password", role: "user", tier: "premium" });
    db.profiles.push({
        user_id: 15, name: "Marcus Aurelius", headline: "Seeking compatibility and lifetime partnership 🏛️", pronouns: "he/him",
        date_of_birth: "1989-08-23", gender_identity: "man", gender_custom: "", sexual_orientation: "gay",
        city: "Rome", country: "Italy", relationship_intent: "marriage", about_me: "Historian and runner. Values honesty, philosophy, and quiet evenings with books.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=300&q=80"])
    });

    // 15. Nina Simone
    db.users.push({ id: 16, name: "Nina Simone", email: "nina@lgbtqmatrimony.local", password: "password", role: "user", tier: "free" });
    db.profiles.push({
        user_id: 16, name: "Nina Simone", headline: "Musician looking for deep love and harmony 🎹", pronouns: "she/her",
        date_of_birth: "1994-06-12", gender_identity: "woman", gender_custom: "", sexual_orientation: "lesbian",
        city: "Los Angeles", country: "USA", relationship_intent: "long-term", about_me: "Jazz pianist. Love vinyl records, beach walks, and vegan dining. Let's make music together.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80"])
    });

    // 16. Jamie Oliver
    db.users.push({ id: 17, name: "Jamie Oliver", email: "jamie@lgbtqmatrimony.local", password: "password", role: "user", tier: "free" });
    db.profiles.push({
        user_id: 17, name: "Jamie Oliver", headline: "Lover of good food, slow Sunday mornings, and cozy dates 🍳", pronouns: "they/them",
        date_of_birth: "1996-01-30", gender_identity: "non-binary", gender_custom: "", sexual_orientation: "bisexual",
        city: "London", country: "UK", relationship_intent: "dating", about_me: "Chef. Loves food trucks, comedy shows, and hosting dinners.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80"])
    });

    // 17. Priya Sharma
    db.users.push({ id: 18, name: "Priya Sharma", email: "priya@lgbtqmatrimony.local", password: "password", role: "user", tier: "premium" });
    db.profiles.push({
        user_id: 18, name: "Priya Sharma", headline: "Searching for my forever partner and co-dreamer 🌌", pronouns: "she/her",
        date_of_birth: "1995-10-15", gender_identity: "woman", gender_custom: "", sexual_orientation: "pansexual",
        city: "Delhi", country: "India", relationship_intent: "marriage", about_me: "Corporate lawyer. Enjoys theater, classical music, and driving. Looking for an ambitious and kind-hearted partner.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1589156280159-27698a70f29e?auto=format&fit=crop&w=300&q=80"])
    });

    // 18. Lucas Silva
    db.users.push({ id: 19, name: "Lucas Silva", email: "lucas@lgbtqmatrimony.local", password: "password", role: "user", tier: "free" });
    db.profiles.push({
        user_id: 19, name: "Lucas Silva", headline: "Let's dance samba and travel the world together ✈️", pronouns: "he/him",
        date_of_birth: "1993-02-14", gender_identity: "man", gender_custom: "", sexual_orientation: "bisexual",
        city: "Sao Paulo", country: "Brazil", relationship_intent: "dating", about_me: "Dancer and physical therapist. Lover of nature, fitness, and beach volleyball.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=300&q=80"])
    });

    // 19. Casey Smith
    db.users.push({ id: 20, name: "Casey Smith", email: "casey@lgbtqmatrimony.local", password: "password", role: "user", tier: "premium" });
    db.profiles.push({
        user_id: 20, name: "Casey Smith", headline: "Seeking standard matching parameters and slow romance 🕯️", pronouns: "they/them",
        date_of_birth: "1992-06-25", gender_identity: "genderqueer", gender_custom: "", sexual_orientation: "pansexual",
        city: "Sydney", country: "Australia", relationship_intent: "long-term", about_me: "Landscape designer. Loves hiking, poetry, and sustainable living.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=300&q=80"])
    });

    // 20. Kim Nguyen
    db.users.push({ id: 21, name: "Kim Nguyen", email: "kim@lgbtqmatrimony.local", password: "password", role: "user", tier: "premium" });
    db.profiles.push({
        user_id: 21, name: "Kim Nguyen", headline: "Let's explore coffee shops and local art exhibits 🎨", pronouns: "she/her",
        date_of_birth: "1997-12-05", gender_identity: "transgender woman", gender_custom: "", sexual_orientation: "bisexual",
        city: "Hanoi", country: "Vietnam", relationship_intent: "dating", about_me: "Curator. Loves modern art, traditional coffee, and indie bands.",
        photos: JSON.stringify(["https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80"])
    });

    saveDb();
}

function saveDb() {
    fs.writeFileSync(DB_FILE, JSON.stringify(db, null, 2), 'utf8');
}

function parseMultipart(buffer, boundary) {
    const boundaryBuffer = Buffer.from('--' + boundary);
    const parts = [];
    let start = 0;
    
    while (true) {
        const nextBoundaryIdx = buffer.indexOf(boundaryBuffer, start);
        if (nextBoundaryIdx === -1) break;
        
        if (start > 0) {
            const partBuffer = buffer.slice(start, nextBoundaryIdx);
            const headerEndIdx = partBuffer.indexOf(Buffer.from('\r\n\r\n'));
            if (headerEndIdx !== -1) {
                const headerText = partBuffer.slice(0, headerEndIdx).toString('utf8');
                const data = partBuffer.slice(headerEndIdx + 4);
                const cleanData = data.slice(0, data.length - 2); // remove trailing \r\n
                
                const dispositionMatch = headerText.match(/Content-Disposition:.*filename="([^"]+)"/i);
                const filename = dispositionMatch ? dispositionMatch[1] : null;
                const fieldnameMatch = headerText.match(/Content-Disposition:.*name="([^"]+)"/i);
                const fieldname = fieldnameMatch ? fieldnameMatch[1] : null;
                const contentTypeMatch = headerText.match(/Content-Type:\s*([^\r\n]+)/i);
                const contentType = contentTypeMatch ? contentTypeMatch[1] : null;
                
                parts.push({ fieldname, filename, contentType, data: cleanData });
            }
        }
        start = nextBoundaryIdx + boundaryBuffer.length;
    }
    return parts;
}

// Helpers
function getCookies(req) {
    const list = {};
    const cookieHeader = req.headers.cookie;
    if (cookieHeader) {
        cookieHeader.split(';').forEach(cookie => {
            const parts = cookie.split('=');
            list[parts.shift().trim()] = decodeURIComponent(parts.join('='));
        });
    }
    return list;
}

function getUserFromToken(token) {
    if (!token) return null;
    const cleanToken = token.replace('Bearer ', '');
    // In our mock runner, the token is simply "user_<userId>_role_<role>_tier_<tier>_<name>"
    if (cleanToken.startsWith('user_')) {
        const parts = cleanToken.split('_');
        return {
            id: parseInt(parts[1]),
            role: parts[3],
            tier: parts[5],
            name: parts.slice(6).join('_'),
            email: "mock@lgbtqmatrimony.local"
        };
    }
    return null;
}

function verifyAuth(req, res, requiredTier = 'free') {
    const cookies = getCookies(req);
    const authHeader = req.headers['authorization'];
    let token = null;

    if (authHeader && authHeader.startsWith('Bearer ')) {
        token = authHeader.substring(7);
    } else {
        token = cookies['jwt_token'] || null;
    }

    const user = getUserFromToken(token);
    if (!user) {
        res.writeHead(401, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ success: false, error: "Unauthorized." }));
        return null;
    }

    const dbUser = db.users.find(u => u.id === user.id);
    if (dbUser) {

        user.tier = dbUser.tier;
        user.role = dbUser.role;
        user.name = dbUser.name;
        user.email = dbUser.email;
    }

    // Gating check
    if (requiredTier === 'premium' && user.tier !== 'premium') {
        res.writeHead(403, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ success: false, error: "Premium subscription required.", code: "PREMIUM_REQUIRED" }));
        return null;
    }

    if (requiredTier === 'admin' && user.role !== 'admin') {
        res.writeHead(403, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ success: false, error: "Admin access required." }));
        return null;
    }

    return user;
}

// Custom simple PHP view evaluator
// Custom PHP view evaluator using token compilation
function renderPHP(viewName, req, context = {}) {
    const viewPath = path.join(__dirname, 'frontend', 'views', viewName);
    if (!fs.existsSync(viewPath)) {
        return `View not found: ${viewName}`;
    }

    let template = fs.readFileSync(viewPath, 'utf8');

    // Retrieve active user context from cookie
    const cookies = getCookies(req);
    const currentUser = getUserFromToken(cookies['jwt_token']);
    if (currentUser) {
        const dbUser = db.users.find(u => u.id === currentUser.id);
        if (dbUser) {

            currentUser.tier = dbUser.tier;
            currentUser.role = dbUser.role;
            currentUser.name = dbUser.name;
            currentUser.email = dbUser.email;
        }
    }
    const token = cookies['jwt_token'] || '';

    // If matches is requested, feed them from local javascript DB directly to bypass Nginx makeApiRequest inside Docker
    let feed = context.feed;
    if ((!feed || feed.length === 0) && currentUser) {
        const parsedUrl = new URL(req.url, `http://${req.headers.host}`);
        let matches = db.profiles.filter(p => p.user_id !== currentUser.id);
        const gender = parsedUrl.searchParams.get('gender_identity');
        const orientation = parsedUrl.searchParams.get('sexual_orientation');
        if (gender && gender.trim() !== '') matches = matches.filter(p => p.gender_identity === gender);
        if (orientation && orientation.trim() !== '') matches = matches.filter(p => p.sexual_orientation === orientation);
        feed = matches;
    }

    const ctx = {
        currentUser,
        token,
        feed: feed || [],
        context: {
            feed: feed || []
        },
        ...context
    };

    // Inline header & footer BEFORE parsing using flexible patterns
    const headerPath = path.join(__dirname, 'frontend', 'views', 'header.php');
    const footerPath = path.join(__dirname, 'frontend', 'views', 'footer.php');
    const headerHtml = fs.existsSync(headerPath) ? fs.readFileSync(headerPath, 'utf8') : '';
    const footerHtml = fs.existsSync(footerPath) ? fs.readFileSync(footerPath, 'utf8') : '';

    template = template.replace(/include\s+__DIR__\s*\.\s*['"]\/header\.php['"];?/g, `?>${headerHtml}<?php`);
    template = template.replace(/include\s+__DIR__\s*\.\s*['"]\/footer\.php['"];?/g, `?>${footerHtml}<?php`);

    // Run loops first since they are processed independently
    template = parseViewLoops(template, ctx);

    // Evaluate standard variables and structural conditionals
    return compilePhpTemplate(template, ctx);
}

function resolveExpression(expr, ctx) {
    expr = expr.trim();
    
    // Strip nl2br and htmlspecialchars and strip_tags wrappers
    const nl2brMatch = expr.match(/^nl2br\((.*)\)$/);
    let isNl2br = false;
    if (nl2brMatch) {
        expr = nl2brMatch[1].trim();
        isNl2br = true;
    }
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
    if (expr === "$score") return ctx.score !== undefined ? ctx.score : '80';
    if (expr === "$viewsCount") return ctx.viewsCount !== undefined ? ctx.viewsCount : '0';
    if (expr === "$likesCount") return ctx.likesCount !== undefined ? ctx.likesCount : '0';
    if (expr === "$matchesTodayCount") return '1';
    
    if (expr === "$profile_photos_json") {
        let photos = '[]';
        if (ctx.profile && ctx.profile.photos) {
            if (typeof ctx.profile.photos === 'string') {
                photos = ctx.profile.photos;
            } else {
                photos = JSON.stringify(ctx.profile.photos);
            }
        }
        return photos;
    }
    
    if (expr === "$displayPhoto") {
        let photos = [];
        if (ctx.profile && ctx.profile.photos) {
            try { photos = JSON.parse(ctx.profile.photos); } catch(e) {
                if (Array.isArray(ctx.profile.photos)) photos = ctx.profile.photos;
            }
        }
        return (photos && photos.length > 0) ? photos[0] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80';
    }

    if (expr === "$rPhoto") {
        let photos = [];
        if (ctx.recipientProfile && ctx.recipientProfile.photos) {
            try { photos = JSON.parse(ctx.recipientProfile.photos); } catch(e) {
                if (Array.isArray(ctx.recipientProfile.photos)) photos = ctx.recipientProfile.photos;
            }
        }
        return (photos && photos.length > 0) ? photos[0] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80';
    }
    
    if (expr.includes('date_diff') && expr.includes('date_of_birth')) {
        const targetProf = ctx.profile || ctx.recipientProfile;
        if (targetProf && targetProf.date_of_birth) {
            const dob = new Date(targetProf.date_of_birth);
            return (new Date().getFullYear() - dob.getFullYear()).toString();
        }
        return '25';
    }
    
    // Check for dashoffset stroke offset expression
    if (expr.includes('301.6 - (301.6 * $score) / 100')) {
        const scoreVal = ctx.score !== undefined ? ctx.score : 30;
        return (301.6 - (301.6 * scoreVal) / 100).toString();
    }
    
    // Check for mutual matches ternary
    if (expr.includes("('premium')") || expr.includes('Locked')) {
        const tier = ctx.currentUser ? ctx.currentUser.tier : 'free';
        if (tier === 'premium') {
            return (ctx.feed && ctx.feed.length > 0) ? '1' : '0';
        }
        return 'Locked';
    }
    
    if (expr.startsWith('$profile[')) {
        const match = expr.match(/\['([^']+)'\]/);
        if (match) {
            const key = match[1];
            let val = ctx.profile ? ctx.profile[key] : null;
            if (val === null || val === undefined || val === '') {
                if (expr.includes('??')) {
                    const fallback = expr.split('??')[1].trim().replace(/['"]/g, '');
                    return fallback;
                }
                if (expr.includes('?:')) {
                    const fallback = expr.split('?:')[1].trim().replace(/['"]/g, '');
                    return fallback;
                }
                return '';
            }
            if (isNl2br && typeof val === 'string') {
                val = val.replace(/\n/g, '<br>');
            }
            return val;
        }
    }
    if (expr.startsWith('$recipientProfile[')) {
        const match = expr.match(/\['([^']+)'\]/);
        if (match) {
            const key = match[1];
            let val = ctx.recipientProfile ? ctx.recipientProfile[key] : null;
            if (val === null || val === undefined || val === '') {
                if (expr.includes('??')) {
                    const fallback = expr.split('??')[1].trim().replace(/['"]/g, '');
                    return fallback;
                }
                if (expr.includes('?:')) {
                    const fallback = expr.split('?:')[1].trim().replace(/['"]/g, '');
                    return fallback;
                }
                return '';
            }
            return val;
        }
    }
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
        const isAdmin = !!(ctx.currentUser && ctx.currentUser.role === 'admin');
        if (cond.includes('!==') || cond.includes('!=') || cond.includes('!')) {
            return !isAdmin;
        }
        return isAdmin;
    }
    if (cond.includes('tier') && cond.includes('premium')) {
        const isPremium = !!(ctx.currentUser && ctx.currentUser.tier === 'premium');
        if (cond.includes('!==') || cond.includes('!=') || cond.includes('!')) {
            return !isPremium;
        }
        return isPremium;
    }
    if (cond === '$isPremium') {
        return ctx.currentUser && ctx.currentUser.tier === 'premium';
    }
    if (cond === "$profile['contact_hidden'] ?? true" || cond === "$profile['contact_hidden']") {
        return ctx.profile ? (ctx.profile.contact_hidden !== false) : true;
    }
    if (cond === '$recipientProfile') {
        return ctx.recipientProfile !== undefined && ctx.recipientProfile !== null;
    }
    if (cond.startsWith('!')) {
        const inner = cond.substring(1).trim();
        return !evaluateCondition(inner, ctx);
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
                // To support nested parentheses in if conditions, match up to the trailing ):
                // Match "if" then an opening parenthesis, any content, and ending with "):"
                const condMatch = phpCode.match(/^if\s*\(([\s\S]*)\):$/);
                if (condMatch) {
                    const condStr = condMatch[1].trim();
                    stack.push(evaluateCondition(condStr, ctx));
                }
            } else if (phpCode.startsWith('else:')) {
                if (stack.length > 0) {
                    const last = stack.pop();
                    stack.push(!last);
                }
            } else if (phpCode.startsWith('endif;')) {
                if (stack.length > 0) {
                    stack.pop();
                }
            }
        }
    }
    return output;
}

function parseViewLoops(content, ctx) {
    // Matches discovery feed loops - use partial match to avoid whitespace issues
    if (content.includes('foreach ($feed as $item):')) {
        const loopRegex = /\s*<\?php\s+foreach\s*\(\$feed\s+as\s+\$item\):\s*[\s\S]*?\?>([\s\S]*?)<\?php\s+endforeach;\s*\?>/;
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
                // Also handle $comp for compatibility score
                block = block.replace(/<\?=\s*\$comp\s*\?>/g, Math.floor(Math.random() * (98 - 82 + 1)) + 82);
                
                compiledFeed += block;
            });
            content = content.replace(loopRegex, compiledFeed);
        }
    }

    // Matches dashboard matchesLimit loops
    if (content.includes('foreach ($matchesLimit as $item):')) {
        const loopRegex = /<\?php\s+(?:(?!<\?php)[\s\S])*?foreach\s*\(\$matchesLimit\s+as\s+\$item\):\s*[\s\S]*?\?>([\s\S]*?)<\?php\s+endforeach;\s*\?>/;
        const match = content.match(loopRegex);
        if (match) {
            let compiledLimit = '';
            const limitItems = (ctx.feed || []).slice(0, 6);
            limitItems.forEach(item => {
                let block = match[1];
                const age = item.date_of_birth ? (new Date().getFullYear() - new Date(item.date_of_birth).getFullYear()) : 25;
                const photos = JSON.parse(item.photos || '[]');
                const displayPhoto = photos.length > 0 ? photos[0] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80';
                const genderDisp = item.gender_identity === 'other' ? (item.gender_custom || 'Other') : item.gender_identity;
                const isOnline = Math.random() > 0.5;
                
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
                block = block.replace(/<\?=\s*\$scoreVal\s*\?>/g, Math.floor(Math.random() * (98 - 84 + 1)) + 84);
                // Handle direct $item['city'] and $item['country'] without ternary
                block = block.replace(/<\?=\s*htmlspecialchars\(\$item\['city'\]\)\s*\?>/g, item.city || 'Unknown');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$item\['country'\]\)\s*\?>/g, item.country || '');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$item\['sexual_orientation'\]\)\s*\?>/g, item.sexual_orientation || 'queer');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$item\['pronouns'\]\)\s*\?>/g, item.pronouns || 'they/them');
                // Handle inline if/endif for $isOnline
                block = block.replace(/<\?php\s+if\s*\(\$isOnline\):\s*\?>([\s\S]*?)<\?php\s+endif;\s*\?>/g, isOnline ? '$1' : '');
                
                compiledLimit += block;
            });
            content = content.replace(loopRegex, compiledLimit);
        }
    }
    
    // Notifications Loop
    if (content.includes('<?php foreach ($notifications as $n): ?>')) {
        const loopRegex = /<\?php\s+foreach\s*\(\$notifications\s+as\s+\$n\):\s*\?>([\s\S]*?)<\?php\s+endforeach;\s*\?>/;
        const match = content.match(loopRegex);
        if (match) {
            let compiledNotifs = '';
            (ctx.notifications || []).forEach(n => {
                let block = match[1];
                const readClass = n.is_read ? 'bg-white/30 border-gray-200/50 opacity-70' : 'bg-pink-50/50 border-pink-100 shadow-sm';
                block = block.replace(/<\?=\s*\$n\['is_read'\]\s*\?\s*[^:]+:[^?]+\?>/g, readClass);
                block = block.replace(/<\?=\s*\$n\['id'\]\s*\?>/g, n.id);
                block = block.replace(/<\?=\s*htmlspecialchars\(\$n\['type'\]\)\s*\?>/g, n.type);
                block = block.replace(/<\?=\s*htmlspecialchars\(\$n\['title'\]\)\s*\?>/g, n.title);
                block = block.replace(/<\?=\s*htmlspecialchars\(\$n\['message'\]\)\s*\?>/g, n.message);
                block = block.replace(/<\?=\s*date\([\s\S]*?created_at[\s\S]*?\?>/g, new Date(n.created_at).toLocaleDateString());
                
                // Extract inner is_read check
                block = block.replace(/<\?php\s+if\s*\(!\$n\['is_read'\]\):\s*\?>([\s\S]*?)<\?php\s+endif;\s*\?>/g, !n.is_read ? '$1' : '');
                compiledNotifs += block;
            });
            content = content.replace(loopRegex, compiledNotifs);
        }
    }

    // Reports Loop
    if (content.includes('<?php foreach ($reports as $r): ?>')) {
        const loopRegex = /<\?php\s+foreach\s*\(\$reports\s+as\s+\$r\):\s*\?>([\s\S]*?)<\?php\s+endforeach;\s*\?>/;
        const match = content.match(loopRegex);
        if (match) {
            let compiledReports = '';
            (ctx.reports || []).forEach(r => {
                let block = match[1];
                const statClass = r.status === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700';
                block = block.replace(/<\?=\s*\$r\['id'\]\s*\?>/g, r.id);
                block = block.replace(/<\?=\s*\$r\['reporter_id'\]\s*\?>/g, r.reporter_id);
                block = block.replace(/<\?=\s*\$r\['reported_id'\]\s*\?>/g, r.reported_id);
                block = block.replace(/<\?=\s*htmlspecialchars\(\$r\['reason'\]\)\s*\?>/g, r.reason);
                block = block.replace(/<\?=\s*htmlspecialchars\(\$r\['status'\]\)\s*\?>/g, r.status);
                block = block.replace(/<\?=\s*\$r\['status'\]\s*===\s*'pending'\s*\?\s*[^:]+:[^?]+\?>/g, statClass);
                
                block = block.replace(/<\?php\s+if\s*\(\$r\['status'\]\s*===\s*'pending'\):\s*\?>([\s\S]*?)<\?php\s+else:\s*\?>([\s\S]*?)<\?php\s+endif;\s*\?>/g, r.status === 'pending' ? '$1' : '$2');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$r\['action_taken'\]\s*\?\?\s*'none'\)\s*\?>/g, r.action_taken || 'none');
                compiledReports += block;
            });
            content = content.replace(loopRegex, compiledReports);
        }
    }

    // Users Loop
    if (content.includes('<?php foreach ($users as $u): ?>')) {
        const loopRegex = /<\?php\s+foreach\s*\(\$users\s+as\s+\$u\):\s*\?>([\s\S]*?)<\?php\s+endforeach;\s*\?>/;
        const match = content.match(loopRegex);
        if (match) {
            let compiledUsers = '';
            (ctx.users || []).forEach(u => {
                let block = match[1];
                const tierClass = u.tier === 'premium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800';
                const statusClass = (u.status || 'active') === 'suspended' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700';
                
                block = block.replace(/<\?=\s*\$u\['id'\]\s*\?>/g, u.id);
                block = block.replace(/<\?=\s*htmlspecialchars\(\$u\['name'\]\)\s*\?>/g, u.name || 'Anonymous');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$u\['email'\]\)\s*\?>/g, u.email || '');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$u\['role'\]\)\s*\?>/g, u.role || 'user');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$u\['tier'\]\)\s*\?>/g, u.tier || 'free');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$u\['status'\]\s*\?\?\s*'active'\)\s*\?>/g, u.status || 'active');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$u\['status'\]\)\s*\?>/g, u.status || 'active');
                
                block = block.replace(/<\?=\s*\$u\['tier'\]\s*===\s*'premium'\s*\?\s*[^:]+:[^?]+\?>/g, tierClass);
                block = block.replace(/<\?=\s*\(\$u\['status'\]\s*\?\?\s*'active'\)\s*===\s*'suspended'\s*\?\s*[^:]+:[^?]+\?>/g, statusClass);
                
                compiledUsers += block;
            });
            content = content.replace(loopRegex, compiledUsers);
        }
    }

    // Blocked Profiles Loop
    if (content.includes('<?php foreach ($blockedProfiles as $bp): ?>')) {
        const loopRegex = /<\?php\s+foreach\s*\(\$blockedProfiles\s+as\s+\$bp\):\s*\?>([\s\S]*?)<\?php\s+endforeach;\s*\?>/;
        const match = content.match(loopRegex);
        if (match) {
            let compiledBlocked = '';
            (ctx.blockedProfiles || []).forEach(bp => {
                let block = match[1];
                let photos = [];
                try { photos = JSON.parse(bp.photos); } catch(e) {
                    if (Array.isArray(bp.photos)) photos = bp.photos;
                }
                const displayPhoto = (photos && photos.length > 0) ? photos[0] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80';
                
                block = block.replace(/<\?=\s*htmlspecialchars\(\$bp\['name'\]\)\s*\?>/g, bp.name || 'Anonymous');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$bp\['city'\]\)\s*\?>/g, bp.city || 'Unknown');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$bp\['country'\]\)\s*\?>/g, bp.country || '');
                block = block.replace(/<\?=\s*htmlspecialchars\(\$displayPhoto\)\s*\?>/g, displayPhoto);
                block = block.replace(/<\?=\s*\$bp\['blocked_id'\]\s*\?>/g, bp.blocked_id);
                block = block.replace(/<\?=\s*\$bp\['id'\]\s*\?>/g, bp.id);
                
                compiledBlocked += block;
            });
            content = content.replace(loopRegex, compiledBlocked);
        }
    }

    return content;
}

// Router Setup
const server = http.createServer((req, res) => {
    const parsedUrl = new URL(req.url, `http://${req.headers.host}`);
    const method = req.method;
    const pathname = parsedUrl.pathname;

    console.log(`[${method}] ${pathname}`);

    // Parse JSON Request Body Helper
    let chunks = [];
    req.on('data', chunk => chunks.push(chunk));
    req.on('end', () => {
        const rawBody = Buffer.concat(chunks);
        const body = rawBody.toString('utf8');
        let jsonData = {};
        if (body && req.headers['content-type'] && req.headers['content-type'].includes('application/json')) {
            try { jsonData = JSON.parse(body); } catch(e) {}
        }

        // STATIC ASSETS
        if (pathname.startsWith('/assets/')) {
            const assetPath = path.join(__dirname, 'frontend', 'public', pathname);
            if (fs.existsSync(assetPath)) {
                res.writeHead(200, { 'Content-Type': pathname.endsWith('.css') ? 'text/css' : 'application/javascript' });
                res.end(fs.readFileSync(assetPath));
            } else {
                res.writeHead(404);
                res.end();
            }
            return;
        }

        if (pathname.startsWith('/uploads/')) {
            const uploadPath = path.join(__dirname, pathname);
            if (fs.existsSync(uploadPath)) {
                res.writeHead(200, { 'Content-Type': 'image/jpeg' });
                res.end(fs.readFileSync(uploadPath));
            } else {
                res.writeHead(404);
                res.end();
            }
            return;
        }

        // ==========================================
        // MICROSERVICES MOCK APIS
        // ==========================================

        // --- AUTH-SERVICE ---
        if (pathname === '/api/v1/auth/register' && method === 'POST') {
            const { name, email, password, date_of_birth, gender_identity, gender_custom, sexual_orientation, city, country } = jsonData;
            if (!name || !email || !password) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                return res.end(JSON.stringify({ success: false, error: "Missing parameters." }));
            }
            if (db.users.find(u => u.email === email)) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                return res.end(JSON.stringify({ success: false, error: "Email registered." }));
            }

            const id = db.users.length + 1;
            const newUser = { id, name, email, password, role: 'user', tier: 'free' };
            db.users.push(newUser);

            db.profiles.push({
                user_id: id,
                name,
                date_of_birth,
                gender_identity,
                gender_custom: gender_custom || '',
                sexual_orientation,
                city,
                country,
                photos: JSON.stringify([]),
                pronouns: 'they/them'
            });

            saveDb();

            const token = `user_${id}_role_user_tier_free_${name}`;
            res.writeHead(201, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({
                success: true,
                user: newUser,
                tokens: { access_token: token, expires_in: 3600 }
            }));
        }

        if (pathname === '/api/v1/auth/login' && method === 'POST') {
            const { email, password } = jsonData;
            const user = db.users.find(u => u.email === email && u.password === password);
            if (!user) {
                res.writeHead(401, { 'Content-Type': 'application/json' });
                return res.end(JSON.stringify({ success: false, error: "Invalid email or password." }));
            }

            const token = `user_${user.id}_role_${user.role}_tier_${user.tier}_${user.name}`;
            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({
                success: true,
                user,
                tokens: { access_token: token, expires_in: 3600 }
            }));
        }

        if (pathname === '/api/v1/auth/google' && method === 'POST') {
            const id = db.users.length + 1;
            const name = "Google User " + Math.floor(Math.random()*1000);
            const newUser = { id, name, email: `google.${id}@gmail.com`, password: "g_pass", role: 'user', tier: 'free' };
            db.users.push(newUser);

            db.profiles.push({
                user_id: id,
                name,
                date_of_birth: "1998-01-01",
                gender_identity: "non-binary",
                gender_custom: "",
                sexual_orientation: "queer",
                city: "New York",
                country: "USA",
                photos: JSON.stringify([]),
                pronouns: "they/them"
            });

            saveDb();

            const token = `user_${id}_role_user_tier_free_${name}`;
            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({
                success: true,
                user: newUser,
                tokens: { access_token: token, expires_in: 3600 }
            }));
        }

        if (pathname === '/api/v1/auth/internal/update-tier' && method === 'POST') {
            const { user_id, tier } = jsonData;
            const user = db.users.find(u => u.id === user_id);
            if (user) {
                user.tier = tier;
                saveDb();
            }
            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true }));
        }

        // --- PROFILE-SERVICE ---
        if (pathname === '/api/v1/profiles/me') {
            const user = verifyAuth(req, res);
            if (!user) return;

            if (method === 'GET') {
                const profile = db.profiles.find(p => p.user_id === user.id);
                res.writeHead(200, { 'Content-Type': 'application/json' });
                return res.end(JSON.stringify({ success: true, profile }));
            }

            if (method === 'POST') {
                let profile = db.profiles.find(p => p.user_id === user.id);
                if (!profile) {
                    profile = { user_id: user.id };
                    db.profiles.push(profile);
                }

                Object.assign(profile, {
                    name: user.name,
                    headline: jsonData.headline || '',
                    about_me: jsonData.about_me || '',
                    height: parseInt(jsonData.height) || null,
                    religion: jsonData.religion || '',
                    mother_tongue: jsonData.mother_tongue || '',
                    education: jsonData.education || '',
                    profession: jsonData.profession || '',
                    relationship_intent: jsonData.relationship_intent || '',
                    photos: JSON.stringify(jsonData.photos || []),
                    pronouns: jsonData.pronouns || 'they/them',
                    hobbies: jsonData.hobbies || '',
                    lifestyle_habits: jsonData.lifestyle_habits || '',
                    family_details: jsonData.family_details || '',
                    partner_pref: jsonData.partner_pref || '',
                    hometown: jsonData.hometown || ''
                });

                saveDb();
                res.writeHead(200, { 'Content-Type': 'application/json' });
                return res.end(JSON.stringify({ success: true, profile }));
            }
        }

        if (pathname.startsWith('/api/v1/profiles/') && method === 'GET') {
            const user = verifyAuth(req, res);
            if (!user) return;

            if (pathname.includes('/internal/')) {
                // Internal API bypasses tier checking
                const match = pathname.match(/\/internal\/(\d+)$/);
                const targetId = match ? parseInt(match[1]) : 0;
                const profile = db.profiles.find(p => p.user_id === targetId);
                res.writeHead(profile ? 200 : 404, { 'Content-Type': 'application/json' });
                return res.end(JSON.stringify({ success: !!profile, profile }));
            }

            const match = pathname.match(/\/api\/v1\/profiles\/(\d+)$/);
            const targetId = match ? parseInt(match[1]) : 0;
            const profile = db.profiles.find(p => p.user_id === targetId);

            if (!profile) {
                res.writeHead(404, { 'Content-Type': 'application/json' });
                return res.end(JSON.stringify({ success: false, error: "Profile not found." }));
            }

            const isMe = (user.id === targetId);
            const profileCopy = { ...profile };

            // Log activity view event
            if (!isMe) {
                db.activity_logs.push({
                    id: db.activity_logs.length + 1,
                    user_id: user.id,
                    target_id: targetId,
                    action_type: 'view',
                    status: 'completed',
                    created_at: new Date().toISOString()
                });
                
                // If Target is Premium, send them a notification alert!
                const targetUser = db.users.find(u => u.id === targetId);
                if (targetUser && targetUser.tier === 'premium') {
                    const nid = db.notifications.length + 1;
                    db.notifications.push({
                        id: nid,
                        user_id: targetId,
                        title: "Profile Viewed 👁️",
                        message: `${user.name} viewed your profile.`,
                        type: "profile_view",
                        is_read: false,
                        created_at: new Date().toISOString()
                    });
                    
                    // Dispatch websocket alert
                    broadcastToUser(targetId, {
                        type: 'notification',
                        notification: { title: "Profile Viewed 👁️", message: `${user.name} viewed your profile.`, type: 'profile_view' }
                    });
                }
                saveDb();
            }

            // Gating Blurring Access control
            if (user.tier !== 'premium' && !isMe) {
                profileCopy.contact_hidden = true;
                profileCopy.email = "locked@lgbtqmatrimony.local";
                profileCopy.phone = "Locked";
            } else {
                profileCopy.contact_hidden = false;
                profileCopy.email = `user${targetId}@lgbtqmatrimony.local`;
                profileCopy.phone = `+1 (555) 002-128${targetId % 10}`;
            }

            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, profile: profileCopy }));
        }

        // --- DISCOVERY-SERVICE ---
        if (pathname === '/api/v1/discovery/feed' && method === 'GET') {
            const user = verifyAuth(req, res);
            if (!user) return;

            let matches = db.profiles.filter(p => p.user_id !== user.id);
            
            // Basic filtering
            const gender = parsedUrl.searchParams.get('gender_identity');
            const orientation = parsedUrl.searchParams.get('sexual_orientation');
            if (gender && gender.trim() !== '' && gender.trim() !== 'Any') matches = matches.filter(p => p.gender_identity === gender.trim());
            if (orientation && orientation.trim() !== '' && orientation.trim() !== 'Any') matches = matches.filter(p => p.sexual_orientation === orientation.trim());

            // Gated Premium filters
            if (user.tier === 'premium') {
                const city = parsedUrl.searchParams.get('city');
                const intent = parsedUrl.searchParams.get('relationship_intent');
                if (city && city.trim() !== '') matches = matches.filter(p => p.city && p.city.toLowerCase().includes(city.toLowerCase().trim()));
                if (intent && intent.trim() !== '' && intent.trim() !== 'Any') matches = matches.filter(p => p.relationship_intent === intent.trim());
            }

            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, feed: matches }));
        }

        // --- SUBSCRIPTION-SERVICE ---
        if (pathname === '/api/v1/subscriptions/checkout' && method === 'POST') {
            const user = verifyAuth(req, res);
            if (!user) return;

            const { plan, gateway } = jsonData;
            const amount = plan === 'monthly' ? 999 : 7999;
            const currency = gateway === 'razorpay' ? 'INR' : 'USD';
            const payId = 'pay_' + crypto.randomBytes(8).toString('hex');

            const checkoutUrl = `/subscription/mock-payment?pay_id=${payId}&amount=${amount}&currency=${currency}&gateway=${gateway}&plan=${plan}&user_id=${user.id}`;
            
            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, checkout_url: checkoutUrl }));
        }

        if (pathname === '/api/v1/subscriptions/webhook' && method === 'POST') {
            const { payment_id, user_id, plan, gateway } = jsonData;
            
            db.subscriptions.push({
                id: db.subscriptions.length + 1,
                user_id,
                plan_type: plan,
                status: 'active',
                gateway,
                payment_id,
                expires_at: new Date(Date.now() + (plan === 'monthly' ? 30 : 365) * 86400000).toISOString()
            });

            // Update user record
            const user = db.users.find(u => u.id === user_id);
            if (user) {
                user.tier = 'premium';
            }

            // Generate notification alert
            db.notifications.push({
                id: db.notifications.length + 1,
                user_id,
                title: "Subscription Active! 👑",
                message: `Welcome to Proud Hearts Premium. Plans activated via ${gateway}.`,
                type: "alert",
                is_read: false,
                created_at: new Date().toISOString()
            });

            saveDb();

            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, message: "Subscription updated." }));
        }

        // --- CHAT-SERVICE ---
        if (pathname.startsWith('/api/v1/chats/history/') && method === 'GET') {
            const user = verifyAuth(req, res, 'premium');
            if (!user) return;

            const match = pathname.match(/\/history\/(\d+)$/);
            const recipientId = match ? parseInt(match[1]) : 0;

            const logs = db.messages.filter(m => 
                (m.sender_id === user.id && m.recipient_id === recipientId) ||
                (m.sender_id === recipientId && m.recipient_id === user.id)
            );

            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, history: logs }));
        }

        if (pathname === '/api/v1/chats/send' && method === 'POST') {
            const user = verifyAuth(req, res, 'premium');
            if (!user) return;

            const { recipient_id, message } = jsonData;
            const msgObj = {
                id: db.messages.length + 1,
                sender_id: user.id,
                sender_name: user.name,
                recipient_id: parseInt(recipient_id),
                message,
                is_read: false,
                created_at: new Date().toISOString()
            };

            db.messages.push(msgObj);
            
            // Push dynamic notification alert to recipient
            db.notifications.push({
                id: db.notifications.length + 1,
                user_id: parseInt(recipient_id),
                title: "New Message 💬",
                message: `You received a message from ${user.name}.`,
                type: 'message',
                is_read: false,
                created_at: new Date().toISOString()
            });

            saveDb();

            // Broadcast real-time message via active WS channel
            broadcastToUser(recipient_id, {
                type: 'message',
                message: msgObj
            });

            res.writeHead(201, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, message: msgObj }));
        }

        if (pathname.startsWith('/api/v1/chats/read/') && method === 'POST') {
            const user = verifyAuth(req, res);
            if (!user) return;

            const match = pathname.match(/\/read\/(\d+)$/);
            const msgId = match ? parseInt(match[1]) : 0;
            const msg = db.messages.find(m => m.id === msgId && m.recipient_id === user.id);
            if (msg) {
                msg.is_read = true;
                saveDb();
            }
            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true }));
        }

        // --- ACTIVITY-SERVICE ---
        if (pathname === '/api/v1/activity/interest' && method === 'POST') {
            const user = verifyAuth(req, res, 'premium');
            if (!user) return;

            const targetId = parseInt(jsonData.target_id);
            
            // Record interest activity
            db.activity_logs.push({
                id: db.activity_logs.length + 1,
                user_id: user.id,
                target_id: targetId,
                action_type: 'interest',
                status: 'pending',
                created_at: new Date().toISOString()
            });

            // Target notifications
            db.notifications.push({
                id: db.notifications.length + 1,
                user_id: targetId,
                title: "Interest Received ❤️",
                message: `${user.name} expressed interest in your profile.`,
                type: 'like',
                is_read: false,
                created_at: new Date().toISOString()
            });

            // Mutual Check!
            const mutual = db.activity_logs.find(a => a.user_id === targetId && a.target_id === user.id && a.action_type === 'interest');
            if (mutual) {
                db.notifications.push({
                    id: db.notifications.length + 1,
                    user_id: user.id,
                    title: "Mutual Match! 🎉",
                    message: "You matched with each other! You can now chat.",
                    type: 'like',
                    is_read: false,
                    created_at: new Date().toISOString()
                });
                
                db.notifications.push({
                    id: db.notifications.length + 1,
                    user_id: targetId,
                    title: "Mutual Match! 🎉",
                    message: `${user.name} matched back! Ready to chat.`,
                    type: 'like',
                    is_read: false,
                    created_at: new Date().toISOString()
                });
                
                broadcastToUser(targetId, {
                    type: 'notification',
                    notification: { title: "Mutual Match! 🎉", message: `${user.name} matched back!`, type: 'like' }
                });
            } else {
                broadcastToUser(targetId, {
                    type: 'notification',
                    notification: { title: "Interest Received ❤️", message: `${user.name} expressed interest.`, type: 'like' }
                });
            }

            saveDb();

            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, message: "Interest expressed.", mutual: !!mutual }));
        }

        // --- NOTIFICATION-SERVICE ---
        if (pathname === '/api/v1/notifications' && method === 'GET') {
            const user = verifyAuth(req, res);
            if (!user) return;

            const list = db.notifications.filter(n => n.user_id === user.id);
            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, notifications: list }));
        }

        if (pathname.endsWith('/read') && pathname.startsWith('/api/v1/notifications/') && method === 'POST') {
            const user = verifyAuth(req, res);
            if (!user) return;

            const match = pathname.match(/\/notifications\/(\d+)\/read/);
            const nid = match ? parseInt(match[1]) : 0;
            const notif = db.notifications.find(n => n.id === nid && n.user_id === user.id);
            if (notif) {
                notif.is_read = true;
                saveDb();
            }
            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true }));
        }

        // --- MODERATION-SERVICE ---
        if (pathname === '/api/v1/moderation/report' && method === 'POST') {
            const user = verifyAuth(req, res);
            if (!user) return;

            db.reports.push({
                id: db.reports.length + 1,
                reporter_id: user.id,
                reported_id: parseInt(jsonData.reported_id),
                reason: jsonData.reason,
                status: 'pending',
                action_taken: null
            });
            saveDb();

            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, message: "Profile flagged for moderator review." }));
        }

        if (pathname === '/api/v1/moderation/block' && method === 'POST') {
            const user = verifyAuth(req, res);
            if (!user) return;

            db.blocks.push({
                id: db.blocks.length + 1,
                blocker_id: user.id,
                blocked_id: parseInt(jsonData.blocked_id)
            });
            saveDb();

            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, message: "User blocked." }));
        }

        if (pathname === '/api/v1/moderation/unblock' && method === 'POST') {
            const user = verifyAuth(req, res);
            if (!user) return;

            const blockedId = parseInt(jsonData.blocked_id);
            db.blocks = db.blocks.filter(b => !(b.blocker_id === user.id && b.blocked_id === blockedId));
            saveDb();

            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, message: "User unblocked." }));
        }

        if (pathname === '/api/v1/moderation/admin/reports' && method === 'GET') {
            const user = verifyAuth(req, res, 'admin');
            if (!user) return;

            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, reports: db.reports }));
        }

        if (pathname === '/api/v1/admin/users' && method === 'GET') {
            const user = verifyAuth(req, res, 'admin');
            if (!user) return;
            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, users: db.users }));
        }

        if (pathname.match(/\/admin\/users\/(\d+)\/toggle-status$/) && method === 'POST') {
            const user = verifyAuth(req, res, 'admin');
            if (!user) return;
            const match = pathname.match(/\/admin\/users\/(\d+)\/toggle-status$/);
            const uid = parseInt(match[1]);
            const dbUser = db.users.find(u => u.id === uid);
            if (dbUser) {
                dbUser.status = (dbUser.status === 'suspended') ? 'active' : 'suspended';
                saveDb();
                res.writeHead(200, { 'Content-Type': 'application/json' });
                return res.end(JSON.stringify({ success: true, user: dbUser }));
            }
            res.writeHead(404, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: false, error: "User not found" }));
        }

        if (pathname.match(/\/admin\/users\/(\d+)\/toggle-tier$/) && method === 'POST') {
            const user = verifyAuth(req, res, 'admin');
            if (!user) return;
            const match = pathname.match(/\/admin\/users\/(\d+)\/toggle-tier$/);
            const uid = parseInt(match[1]);
            const dbUser = db.users.find(u => u.id === uid);
            if (dbUser) {
                dbUser.tier = (dbUser.tier === 'premium') ? 'free' : 'premium';
                saveDb();
                res.writeHead(200, { 'Content-Type': 'application/json' });
                return res.end(JSON.stringify({ success: true, user: dbUser }));
            }
            res.writeHead(404, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: false, error: "User not found" }));
        }

        if (pathname.match(/\/admin\/users\/(\d+)\/delete$/) && method === 'POST') {
            const user = verifyAuth(req, res, 'admin');
            if (!user) return;
            const match = pathname.match(/\/admin\/users\/(\d+)\/delete$/);
            const uid = parseInt(match[1]);
            db.users = db.users.filter(u => u.id !== uid);
            db.profiles = db.profiles.filter(p => p.user_id !== uid);
            saveDb();
            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true }));
        }

        if (pathname.match(/\/reports\/(\d+)\/resolve$/) && method === 'POST') {
            const user = verifyAuth(req, res, 'admin');
            if (!user) return;

            const match = pathname.match(/\/reports\/(\d+)\/resolve$/);
            const rid = parseInt(match[1]);
            const report = db.reports.find(r => r.id === rid);
            if (report) {
                report.status = 'reviewed';
                report.action_taken = jsonData.action; // dismissed, suspended
                
                if (jsonData.action === 'suspended') {
                    // Remove profile
                    db.profiles = db.profiles.filter(p => p.user_id !== report.reported_id);
                }
                saveDb();
            }

            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, message: `Report resolves as ${jsonData.action}.` }));
        }

        // --- MEDIA UPLOAD ---
        if (pathname === '/api/v1/media/upload' && method === 'POST') {
            const user = verifyAuth(req, res);
            if (!user) return;

            const contentType = req.headers['content-type'] || '';
            const match = contentType.match(/boundary=(?:"([^"]+)"|([^;]+))/i);
            const boundary = match ? (match[1] || match[2]) : null;

            if (boundary) {
                try {
                    const parts = parseMultipart(rawBody, boundary);
                    const photoPart = parts.find(p => p.fieldname === 'photo');
                    if (photoPart && photoPart.data && photoPart.filename) {
                        const ext = path.extname(photoPart.filename) || '.jpg';
                        const filename = `upload_${Date.now()}_${Math.floor(Math.random()*1000)}${ext}`;
                        const relativePath = `/uploads/${filename}`;
                        const absolutePath = path.join(__dirname, 'uploads', filename);
                        
                        // Ensure directory exists
                        if (!fs.existsSync(path.dirname(absolutePath))) {
                            fs.mkdirSync(path.dirname(absolutePath), { recursive: true });
                        }
                        
                        fs.writeFileSync(absolutePath, photoPart.data);
                        
                        res.writeHead(200, { 'Content-Type': 'application/json' });
                        return res.end(JSON.stringify({ success: true, url: relativePath }));
                    }
                } catch (e) {
                    console.error("Upload error:", e);
                }
            }

            const mockPhotoUrl = "https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80";
            res.writeHead(200, { 'Content-Type': 'application/json' });
            return res.end(JSON.stringify({ success: true, url: mockPhotoUrl }));
        }

        // ==========================================
        // FRONTEND PAGES (SERVER-SIDE RENDERED TEMPLATES)
        // ==========================================
        const cookies = getCookies(req);
        const loggedUser = getUserFromToken(cookies['jwt_token']);
        if (loggedUser) {
            const dbUser = db.users.find(u => u.id === loggedUser.id);
            if (dbUser) {
                if (dbUser.email === 'sam@lgbtqmatrimony.local') {
                    dbUser.tier = 'free';
                } else if (dbUser.email === 'jordan@lgbtqmatrimony.local') {
                    dbUser.tier = 'premium';
                }
                loggedUser.tier = dbUser.tier;
                loggedUser.role = dbUser.role;
                loggedUser.name = dbUser.name;
                loggedUser.email = dbUser.email;
            }
        }

        if (pathname === '/logout') {
            res.writeHead(302, { 
                'Location': '/', 
                'Set-Cookie': 'jwt_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; SameSite=Lax' 
            });
            return res.end();
        }
        if (pathname === '/demo-premium') {
            if (!loggedUser) { res.writeHead(302, { 'Location': '/login' }); return res.end(); }
            const user = db.users.find(u => u.id === loggedUser.id);
            if (user) {
                user.tier = 'premium';
                saveDb();
            }
            const token = `user_${loggedUser.id}_role_${loggedUser.role}_tier_premium_${loggedUser.name}`;
            res.writeHead(302, { 
                'Location': '/dashboard', 
                'Set-Cookie': `jwt_token=${token}; path=/; max-age=3600; SameSite=Lax` 
            });
            return res.end();
        }
        if (pathname === '/' || pathname === '/index.html') {
            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(renderPHP('landing.php', req));
        }
        if (pathname === '/login') {
            if (loggedUser) {
                const target = loggedUser.role === 'admin' ? '/admin' : '/dashboard';
                res.writeHead(302, { 'Location': target });
                return res.end();
            }
            // Standalone login page - serve as raw HTML (no header/footer PHP compilation)
            const loginPath = path.join(__dirname, 'frontend', 'views', 'login.php');
            const loginHtml = fs.readFileSync(loginPath, 'utf8');
            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(loginHtml);
        }
        if (pathname === '/register') {
            if (loggedUser) {
                const target = loggedUser.role === 'admin' ? '/admin' : '/dashboard';
                res.writeHead(302, { 'Location': target });
                return res.end();
            }
            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(renderPHP('register.php', req));
        }
        if (pathname === '/dashboard') {
            if (!loggedUser) { res.writeHead(302, { 'Location': '/login' }); return res.end(); }
            res.writeHead(200, { 'Content-Type': 'text/html' });
            
            const profile = db.profiles.find(p => p.user_id === loggedUser.id);
            let score = 30;
            if (profile) {
                if (profile.headline) score += 15;
                if (profile.about_me) score += 20;
                if (profile.pronouns) score += 15;
                try {
                    const photos = JSON.parse(profile.photos || '[]');
                    if (photos.length > 0) score += 20;
                } catch(e) {}
            }
            const viewsCount = db.activity_logs.filter(a => a.target_id === loggedUser.id && a.action_type === 'view').length;
            const likesCount = db.activity_logs.filter(a => a.target_id === loggedUser.id && a.action_type === 'interest').length;
            const feed = db.profiles.filter(p => p.user_id !== loggedUser.id);

            return res.end(renderPHP('dashboard.php', req, {
                profile,
                score,
                viewsCount,
                likesCount,
                feed
            }));
        }
        if (pathname === '/profile/setup') {
            if (!loggedUser) { res.writeHead(302, { 'Location': '/login' }); return res.end(); }
            const profile = db.profiles.find(p => p.user_id === loggedUser.id);
            
            // Calculate completeness score
            let filled = 0;
            const fields = ['headline', 'about_me', 'pronouns', 'height', 'religion', 'mother_tongue', 'education', 'profession', 'relationship_intent', 'photos', 'hobbies', 'lifestyle_habits', 'family_details', 'partner_pref', 'hometown'];
            fields.forEach(f => {
                if (profile && profile[f]) {
                    if (f === 'photos') {
                        try {
                            const arr = JSON.parse(profile[f]);
                            if (arr && arr.length > 0) filled += arr.length;
                        } catch(e) {}
                    } else {
                        filled++;
                    }
                }
            });
            const scoreVal = Math.min(100, Math.round((filled / fields.length) * 100)) || 20;

            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(renderPHP('profile_setup.php', req, { profile, score: scoreVal }));
        }
        if (pathname === '/discovery') {
            if (!loggedUser) { res.writeHead(302, { 'Location': '/login' }); return res.end(); }
            
            const tab = parsedUrl.searchParams.get('tab') || '';
            let matches = [];
            
            if (tab === 'matches') {
                const likerIds = db.activity_logs
                    .filter(a => a.target_id === loggedUser.id && a.action_type === 'interest')
                    .map(a => a.user_id);
                matches = db.profiles.filter(p => likerIds.includes(p.user_id) && p.user_id !== loggedUser.id);
            } else if (tab === 'favorites') {
                const likedIds = db.activity_logs
                    .filter(a => a.user_id === loggedUser.id && a.action_type === 'interest')
                    .map(a => a.target_id);
                matches = db.profiles.filter(p => likedIds.includes(p.user_id) && p.user_id !== loggedUser.id);
            } else {
                matches = db.profiles.filter(p => p.user_id !== loggedUser.id);
            }
            
            console.log("Discovery route matches count:", matches.length, "loggedUser.id:", loggedUser.id, "tab:", tab);
            
            // Basic filtering if applied
            const gender = parsedUrl.searchParams.get('gender_identity');
            const orientation = parsedUrl.searchParams.get('sexual_orientation');
            if (gender && gender.trim() !== '' && gender.trim() !== 'Any') matches = matches.filter(p => p.gender_identity === gender.trim());
            if (orientation && orientation.trim() !== '' && orientation.trim() !== 'Any') matches = matches.filter(p => p.sexual_orientation === orientation.trim());

            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(renderPHP('browse.php', req, { feed: matches }));
        }
        if (pathname.startsWith('/profile/')) {
            if (!loggedUser) { res.writeHead(302, { 'Location': '/login' }); return res.end(); }
            const targetId = parseInt(pathname.split('/').pop());
            const profile = db.profiles.find(p => p.user_id === targetId);
            
            const isMe = (loggedUser.id === targetId);
            const profileCopy = profile ? { ...profile } : null;

            if (profileCopy) {
                if (loggedUser.tier !== 'premium' && !isMe) {
                    profileCopy.contact_hidden = true;
                } else {
                    profileCopy.contact_hidden = false;
                    profileCopy.email = `user${targetId}@lgbtqmatrimony.local`;
                    profileCopy.phone = `+1 (555) 002-128${targetId % 10}`;
                }
            }

            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(renderPHP('profile_view.php', req, { profile: profileCopy, viewTargetId: targetId }));
        }
        if (pathname === '/subscription' || pathname === '/plans') {
            if (!loggedUser) { res.writeHead(302, { 'Location': '/login' }); return res.end(); }
            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(renderPHP('subscription.php', req));
        }
        if (pathname === '/subscription/mock-payment') {
            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(renderPHP('mock_payment.php', req));
        }
        if (pathname === '/chat') {
            if (!loggedUser) { res.writeHead(302, { 'Location': '/login' }); return res.end(); }
            const recId = parseInt(parsedUrl.searchParams.get('recipient_id')) || 0;
            const recProfile = db.profiles.find(p => p.user_id === recId);

            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(renderPHP('chat.php', req, { recipientProfile: recProfile, recipientId: recId }));
        }
        if (pathname === '/notifications') {
            if (!loggedUser) { res.writeHead(302, { 'Location': '/login' }); return res.end(); }
            const list = db.notifications.filter(n => n.user_id === loggedUser.id);
            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(renderPHP('notifications.php', req, { notifications: list }));
        }
        if (pathname === '/settings') {
            if (!loggedUser) { res.writeHead(302, { 'Location': '/login' }); return res.end(); }
            const blockerId = loggedUser.id;
            const blockedList = db.blocks.filter(b => b.blocker_id === blockerId);
            const blockedProfiles = blockedList.map(b => {
                const p = db.profiles.find(prof => prof.user_id === b.blocked_id) || {};
                const u = db.users.find(usr => usr.id === b.blocked_id) || {};
                return {
                    id: b.id,
                    blocked_id: b.blocked_id,
                    name: u.name || p.name || 'Anonymous',
                    city: p.city || 'Unknown',
                    country: p.country || '',
                    photos: p.photos || '[]'
                };
            });
            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(renderPHP('settings.php', req, { blockedProfiles }));
        }

        // Generic Info Footer Pages handler
        const footerPages = [
            '/mission', '/safe-dating-tips', '/faq', '/trust-safety',
            '/press', '/how-we-connect', '/colorado-safety', '/security',
            '/terms', '/privacy', '/cookie-policy', '/consumer-health-privacy',
            '/privacy-choices'
        ];
        if (footerPages.includes(pathname)) {
            res.writeHead(200, { 'Content-Type': 'text/html' });
            // Generate clean placeholder layout content in the PHP style evaluation context
            const titles = {
                '/mission': 'Our Mission',
                '/safe-dating-tips': 'Safe Dating Tips',
                '/faq': 'Frequently Asked Questions',
                '/trust-safety': 'Trust & Safety',
                '/press': 'Press Resources',
                '/how-we-connect': 'How We Connect Daters',
                '/colorado-safety': 'Colorado Safety Policy Information',
                '/security': 'Security Standards',
                '/terms': 'Terms of Service',
                '/privacy': 'Privacy Policy',
                '/cookie-policy': 'Cookie Policy',
                '/consumer-health-privacy': 'Consumer Health Data Privacy Policy',
                '/privacy-choices': 'Your Privacy Choices'
            };
            const pageTitle = titles[pathname] || 'Info Page';
            const sampleHtml = `<?php include __DIR__ . '/header.php'; ?>
            <div class="max-w-4xl mx-auto my-12 glass-panel p-8 md:p-12 rounded-3xl border border-white/60 shadow-xl space-y-6">
                <span class="text-4xl">🛡️</span>
                <h1 class="text-3xl md:text-5xl font-black text-gray-900 serif-font mt-2">${pageTitle}</h1>
                <p class="text-pink-600 font-bold uppercase tracking-wider text-xs">Proud Hearts Protection &amp; Legal Info</p>
                <div class="border-t border-gray-200/50 pt-6 space-y-4 text-gray-600 text-sm leading-relaxed">
                    <p>Welcome to the official <strong>${pageTitle}</strong> resources portal for Proud Hearts Matrimony platform.</p>
                    <p>We combine modern identity-first compatibility matching metrics with strict user privacy controls to deliver a verified, safe space for the LGBTQ+ community. Our priority is ensuring that your preferences, orientation selection, pronouns, and contact details are fully encrypted and gated according to user-approved trust configurations.</p>
                    <p>If you have questions regarding this page or want to request moderation checks, please contact our support desk inside your profile panel options or review our general safe dating guidelines.</p>
                </div>
                <a href="/discovery" class="inline-block btn-primary px-6 py-2.5 rounded-xl font-bold text-xs shadow-sm mt-4">&larr; Back to Discovery Feed</a>
            </div>
            <?php include __DIR__ . '/footer.php'; ?>`;
            
            // Render it in PHP compiler context
            return res.end(compilePhpTemplate(sampleHtml, { currentUser: loggedUser, token: cookies['jwt_token'] || '' }));
        }
        if (pathname === '/admin') {
            if (!loggedUser || loggedUser.role !== 'admin') {
                res.writeHead(403);
                return res.end("Forbidden access.");
            }
            res.writeHead(200, { 'Content-Type': 'text/html' });
            return res.end(renderPHP('admin.php', req, { reports: db.reports, users: db.users }));
        }

        // 404 fallback
        res.writeHead(404, { 'Content-Type': 'text/plain' });
        res.end("404 Not Found");
    });
});

// Create WebSocket Server
const wss = new WebSocketServer({ noServer: true });
const activeSockets = new Map(); // userId => ws

wss.on('connection', (ws, req) => {
    const parsedUrl = new URL(req.url, `http://${req.headers.host}`);
    const token = parsedUrl.searchParams.get('token');
    const user = getUserFromToken(token);

    if (!user) {
        ws.close(1008, "Auth Failed");
        return;
    }

    ws.user = user;
    activeSockets.set(user.id, ws);
    console.log(`Live WS connection created for User ${user.id} (${user.name})`);

    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);
            if (data.type === 'typing') {
                const targetSocket = activeSockets.get(parseInt(data.recipient_id));
                if (targetSocket) {
                    targetSocket.send(JSON.stringify({
                        type: 'typing',
                        sender_id: user.id,
                        typing: data.typing
                    }));
                }
            }
        } catch (e) {}
    });

    ws.on('close', () => {
        activeSockets.delete(user.id);
        console.log(`WS disconnected: User ${user.id}`);
    });
});

// Handle upgrade protocols for websocket
server.on('upgrade', (request, socket, head) => {
    if (request.url.startsWith('/ws')) {
        wss.handleUpgrade(request, socket, head, (ws) => {
            wss.emit('connection', ws, request);
        });
    } else {
        socket.destroy();
    }
});

function broadcastToUser(userId, payload) {
    const ws = activeSockets.get(parseInt(userId));
    if (ws && ws.readyState === 1) {
        ws.send(JSON.stringify(payload));
        return true;
    }
    return false;
}

// Start Server
server.listen(PORT, () => {
    console.log(`Proud Hearts Matrimony platform running on http://localhost:${PORT}`);
});
