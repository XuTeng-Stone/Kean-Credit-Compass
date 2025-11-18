import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import '../styles/LandingPage.css';

function LandingPage() {
  const navigate = useNavigate();

  return (
    <div className="landing-container">
      <div className="logo-section">
        <img src="/logo.png" alt="AI Study Assistant" className="logo-img" />
      </div>

      <h2 className="subtitle">Welcome to</h2>
      <h1 className="title">Kean-Credit-Compass</h1>

      <div className="start-section">
        <button className="start-btn" onClick={() => navigate("/upload")}>
          Start Checking
        </button>
        <div className="topic-tag">Made by ...</div>
      </div>
    </div>
  );
}

export default LandingPage;






