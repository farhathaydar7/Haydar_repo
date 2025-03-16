import React, { useState } from 'react';
import API from '../services/API';
import { useNavigate } from 'react-router-dom';
import memoriesIcon from '../assets/Icon/memories_icon.png';
import './component.css/Login.css';

function Login() {
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError('');

    try {
      const response = await API.login({
        email: email.trim().toLowerCase(),
        password: password.trim()
      });

      // Axios wraps responses in data property
      const { token, user } = response.data;

      if (token && user) {
        localStorage.setItem('jwt_token', token);
        localStorage.setItem('user', JSON.stringify(user));
        navigate('/gallery', { replace: true });
      } else {
        setError('Invalid response from server');
      }
    } catch (error) {
      // Handle Axios error structure
      const errorMessage = error.response?.data?.error || 
                         error.message || 
                         'Failed to connect to server';
      setError(errorMessage);
      console.error('Login error:', error);
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
            <label htmlFor="email" className="field-label">
              Email
              <a href="#forgot-email" className="forgot-link">
                Forgot e-mail?
              </a>
            </label>
            <input
              type="email"
              id="email"
              placeholder="example@mail.com"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              autoComplete="username"
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
              autoComplete="current-password"
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