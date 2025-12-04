# Testing Guide

Share your app with others for testing.

---

## Option 1: Local Network (Recommended)

### Requirements
- Same WiFi network
- Firewall allows access

### Steps

**Your Computer:**
1. Start services: `npm start` + Apache
2. Find your IP: `ipconfig` → look for IPv4 (e.g., 10.126.20.36)

**Tester's Device:**
1. Open browser
2. Go to: `http://YOUR_IP:3000`
3. Done

### Windows Firewall (if blocked)

```powershell
# Run as Admin
New-NetFirewallRule -DisplayName "Node Port 3000" -Direction Inbound -LocalPort 3000 -Protocol TCP -Action Allow
New-NetFirewallRule -DisplayName "Apache Port 80" -Direction Inbound -LocalPort 80 -Protocol TCP -Action Allow
```

---

## Option 2: ngrok (Internet Access)

### Why?
- Test from anywhere
- No network restrictions
- Temporary HTTPS URL

### Steps

1. **Download ngrok**
   - https://ngrok.com/download
   - Sign up for free token

2. **Start frontend tunnel**
   ```bash
   ngrok http 3000
   ```
   Output: `https://abc123.ngrok.io`

3. **Start backend tunnel** (new terminal)
   ```bash
   ngrok http 80
   ```
   Output: `https://def456.ngrok.io`

4. **Update config** (`src/config.js`)
   ```javascript
   export const API_BASE_URL = 'https://def456.ngrok.io/kcc-backend/api';
   ```

5. **Share frontend URL**
   - Send `https://abc123.ngrok.io` to testers
   - Works from anywhere

---

## Option 3: Cloud Deploy (Permanent)

### Frontend: Netlify/Vercel
```bash
npm run build
# Upload build/ folder
```

### Backend: Any PHP host
- Upload `kcc-backend/` files
- Update `config.php` with DB credentials

---

## Test Checklist

Testers should verify:
- [ ] Landing page loads
- [ ] Click "Start Checking"
- [ ] Select major (CS or IT)
- [ ] Upload CSV (use sample)
- [ ] See validation success
- [ ] View progress results
- [ ] Expand/collapse categories
- [ ] See completed courses
- [ ] Click "View Available" buttons

---

## Troubleshooting

**Can't access local network URL**
- Check firewall rules
- Verify same WiFi
- Test: `ping YOUR_IP`

**CORS error**
- Add IP to `compare_courses.php` allowed origins
```php
$allowed = ['http://localhost:3000', 'http://YOUR_IP:3000'];
```

**Backend fails**
- Check Apache running in XAMPP
- Test: `http://YOUR_IP/kcc-backend/api/get_program_requirements.php?code=BS-CPS`

**ngrok not working**
- Restart tunnels
- Check token: `ngrok config add-authtoken YOUR_TOKEN`

---

## Current URLs

**Local:**
- Frontend: http://localhost:3000
- Backend: http://localhost/kcc-backend/api/

**Network (replace YOUR_IP):**
- Frontend: http://YOUR_IP:3000
- Backend: http://YOUR_IP/kcc-backend/api/

---

## Quick Debug

```bash
# Check services
Get-Process -Name "node" -ErrorAction SilentlyContinue
Get-Process -Name "httpd" -ErrorAction SilentlyContinue

# Test backend
curl http://localhost/kcc-backend/api/get_program_requirements.php?code=BS-CPS
```
