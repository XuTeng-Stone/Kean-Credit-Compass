# 📘 Kean Credit Compass

A lightweight web tool to help **Kean Computer Science students** track their academic progress toward graduation.  
Students can enter completed courses, and the system automatically compares them with program requirements to show what is still outstanding.

---

## 🚀 Project Overview
- **Frontend**: React (TypeScript)  
- **Backend**: Node.js + Express  
- **Database**: TBD (initially JSON/CSV; can extend to SQL)  
- **Deployment**: Cloud-based (Netlify for frontend, Render/Heroku for backend)  

---

## 📂 Project Structure


kean-credit-compass/
- frontend/        # React app (UI for course input & progress display)
- backend/         # Express API (handles logic & data processing)
- docs/            # Documentation, proposals, API design
- README.md        # Project documentation


---

## ⚙️ Setup Instructions

1. Clone the repository
```bash 
git clone https://github.com/YOUR_USERNAME/Kean-Credit-Compass.git
cd Kean-Credit-Compass
```
2. Frontend setup
```bash   
cd kcc-frontend
cd my-app
npm install
npm start
```
3. Backend setup
```bash
cd kcc-backend
npm install
npm run dev   # if using nodemon
```

---

## 🌱 Branch Workflow

### Main branches
- **main** → always stable & production-ready  
- **kcc-frontend** → initial React app setup  
- **kcc-backend** → Express server + API skeleton  
- **feature/** → for specific features (e.g., `feature/progress-api`)  

### Branch naming convention
- `feature/<feature-name>` → new feature development  
- `fix/<bug-name>` → bug fixes  
- `docs/<topic>` → documentation updates  
---

## 👥 Team Members

- Mitch → Backend   
- Stone → Frontend  
- Xianyang → Frontend
  
---

## 📑 Deliverables
- Deployed MVP web app
- GitHub repo with source code & docs
- User guide with screenshots
- Final presentation & demo
