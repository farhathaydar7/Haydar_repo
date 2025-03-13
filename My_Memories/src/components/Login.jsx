import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import API_URL from '../assets/links';
import HEADERS from '../assets/headers';
import memoriesIcon from '../assets/Icon/memories_icon.png';
import './component.css/Login.css';

function Login() {
  const navigate = useNavigate();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError('');

    try {
      const response = await fetch(API_URL + 'v0.1/login.php', {
        method: 'POST',
        headers: HEADERS,
        body: JSON.stringify({ email: username, password }),
      });

      let data;
      try {
        data = await response.json();
      } catch (e) {
        setError('Failed to parse response');
        console.error('JSON parse error:', e);
        return;
      }

      if (response.ok && data.token) {
        localStorage.setItem('jwt_token', data.token);
        alert('Login successful!');
        navigate('/gallery');
      } else {
        setError(data.error || 'Login failed');
      }
    } catch (e) {
      setError('Failed to connect to server');
      console.error('Login error:', e);
    }
  };

  return (
    <div className="login-page">
      {/* Left panel (75%) */}
      <div className="login-left">
        <div className="header-section">
          <img
            src={memoriesIcon}
            alt="Memories Icon"
            className="memories-icon"
          />
          <h1 className="brand-name">My Memories</h1>
          <h3 className="login-title">Login to Your Account</h3>
        </div>

        <form className="login-form" onSubmit={handleSubmit}>
          {error && <p className="error-message">{error}</p>}

          {/* Email input */}
          <div className="input-group">
            <label htmlFor="username" className="field-label">
              Email
              <a href="#forgot-email" className="forgot-link">
                Forgot e-mail?
              </a>
            </label>
            <input
              type="text"
              id="username"
              placeholder="example@mail.com"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              required
            />
          </div>

          {/* Password input */}
          <div className="input-group">
            <label htmlFor="password" className="field-label">
              Password
              <a href="#forgot-password" className="forgot-link">
                Forgot password?
              </a>
            </label>
            <input
              type="password"
              id="password"
              placeholder="your password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>

          <button type="submit" className="login-button">
            Login
          </button>
        </form>
      </div>

      {/* Right panel (25%) */}
      <div className="login-right">
        <h2 className="new-here">New Here?</h2>
        <p className="discover-text">Sign-up and discover endless opportunities</p>
        <button
          type="button"
          className="signup-button"
          onClick={() => navigate('/register')}
        >
          Sign-up
        </button>
      </div>
    </div>
  );
}

export default Login;
