# Kean Credit Compass

A lightweight web tool to help **Kean Computer Science students** track their academic progress toward graduation.  
Students can enter completed courses, and the system automatically compares them with program requirements to show what is still outstanding.

---

## Project Overview

- **Frontend**: React (JavaScript)  
- **Data Processing**: CSV-based (client-side processing)  
- **Deployment**: Cloud-based (Netlify, Vercel, or GitHub Pages)  

---

## Prerequisites

Before running this project, ensure you have the following installed:

- **Node.js**: v14.x or higher
- **npm**: v6.x or higher (comes with Node.js)
- **Git**: Latest version
- **Web Browser**: Chrome, Firefox, Safari, or Edge (latest version)

To check your installed versions:

```bash
node --version
npm --version
git --version
```

---

## Project Structure

```
Kean-Credit-Compass-main/
├── kcc-frontend/
│   └── my-app/
│       ├── public/
│       │   ├── index.html
│       │   ├── logo.png
│       │   ├── manifest.json
│       │   ├── robots.txt
│       │   ├── sample-courses.csv
│       │   └── sample-invalid.csv
│       ├── src/
│       │   ├── components/
│       │   │   └── ui/
│       │   │       ├── button.jsx
│       │   │       ├── card.jsx
│       │   │       └── input.jsx
│       │   ├── Pages/
│       │   │   ├── CourseUpload.jsx
│       │   │   ├── DegreeProgress.jsx
│       │   │   └── LandingPage.jsx
│       │   ├── styles/
│       │   │   ├── CourseUpload.css
│       │   │   ├── DegreeProgress.css
│       │   │   └── LandingPage.css
│       │   ├── App.js
│       │   ├── index.js
│       │   └── index.css
│       ├── package.json
│       └── package-lock.json
├── LICENSE
├── README.md
└── USER_MANUAL.md
```

---

## Setup Instructions

### 1. Clone the Repository

```bash 
git clone https://github.com/YOUR_USERNAME/Kean-Credit-Compass.git
cd Kean-Credit-Compass-main
```

### 2. Frontend Setup

Navigate to the frontend directory and install dependencies:

```bash   
cd kcc-frontend/my-app
npm install
```

### 3. Environment Configuration

The application uses default React configuration. No additional environment variables are required for local development.

### 4. Run the Application

Start the development server:

```bash
npm start
```

The application will automatically open in your default browser at `http://localhost:3000`

If it doesn't open automatically, manually navigate to:
- **Local**: http://localhost:3000
- **Network**: http://YOUR_IP:3000

### 5. Build for Production

To create an optimized production build:

```bash
npm run build
```

This creates a `build/` folder with optimized static files ready for deployment.

---

## Available Scripts

In the project directory (`kcc-frontend/my-app/`), you can run:

### `npm start`
Runs the app in development mode with hot-reloading enabled.

### `npm test`
Launches the test runner in interactive watch mode.

### `npm run build`
Builds the app for production to the `build` folder.

### `npm run eject`
**Note: this is a one-way operation. Once you eject, you can't go back!**

---

## Developer Guidelines

### Testing

This project uses Jest and React Testing Library.

Test files should be placed in the same directory as the component and named `ComponentName.test.js`.

Run tests:
```bash
npm test                          # Watch mode
npm test -- --coverage            # With coverage
npm test -- --watchAll=false      # Single run
```

Basic test example:
```javascript
import { render, screen } from '@testing-library/react';
import ComponentName from './ComponentName';

test('should render correctly', () => {
  render(<ComponentName />);
  expect(screen.getByText('Expected Text')).toBeInTheDocument();
});
```

Guidelines:
- Test user-facing behavior, not implementation
- Maintain 70%+ code coverage for new features
- Run tests before committing

---

## Deployment

### Frontend Deployment (Netlify)

1. Build the production version:
   ```bash
   npm run build
   ```

2. Deploy to Netlify:
   - Connect your GitHub repository to Netlify
   - Set build command: `npm run build`
   - Set publish directory: `build`
   - Deploy

Alternatively, use Netlify CLI:
```bash
npm install -g netlify-cli
netlify deploy --prod
```

### Alternative Deployment Options

**Vercel:**
```bash
npm install -g vercel
vercel --prod
```

**GitHub Pages:**
```bash
npm install --save-dev gh-pages
npm run build
npm run deploy
```

---

## Branch Workflow

### Main Branches
- **main** → Always stable and production-ready  
- **kcc-frontend** → Initial React app setup  
- **feature/** → For specific features (e.g., `feature/csv-validation`)  

### Branch Naming Convention
- `feature/<feature-name>` → New feature development  
- `fix/<bug-name>` → Bug fixes  
- `docs/<topic>` → Documentation updates  

### Workflow Process
1. Create a new branch from `main`
2. Make your changes
3. Test thoroughly
4. Submit a pull request
5. Code review by team members
6. Merge to `main` after approval

---

## Team Members

| Name | Role | Responsibilities |
|------|------|-----------------|
| Mitch | Backend Developer | Server-side logic, API development, data processing |
| Stone | Frontend Developer | UI/UX implementation, component development |
| Xianyang | Frontend Developer | UI/UX implementation, styling, user interaction |

---

## Features

- **Course Upload**: Upload CSV files containing completed courses
- **Data Validation**: Real-time validation of CSV format and data integrity
- **Progress Tracking**: Visual representation of degree completion progress
- **Major Selection**: Support for Computer Science and IT majors
- **Responsive Design**: Works on desktop, tablet, and mobile devices

---

## Technology Stack

**Frontend:**
- React 18.x
- React Router DOM
- CSS3 (Custom styling)
- JavaScript (ES6+)

**Tools:**
- npm (Package management)
- Git (Version control)
- VSCode (Recommended IDE)

---

## Project Deliverables

- Deployed MVP web application
- GitHub repository with source code and documentation
- User manual with usage instructions
- Final presentation and live demo
- Technical documentation

---

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## Bug Reporting

Open an issue on GitHub with title format: `[BUG] Brief description`

Include:
1. Steps to reproduce
2. Expected vs actual behavior
3. Browser, OS, and environment details
4. Screenshots or error messages
5. Sample CSV file if relevant

Example:
```
[BUG] CSV validation fails for valid file

Steps:
1. Select Computer Science major
2. Upload valid CSV with 5 courses
3. Validation shows "Invalid file format" error

Expected: File validates successfully
Actual: Validation error displayed

Environment: Chrome 120, Windows 11, localhost:3000
```

---

## License

This project is licensed under the terms specified in the LICENSE file.

---

## Support

For questions or issues, please contact the development team or open an issue on GitHub.
