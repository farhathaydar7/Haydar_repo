import React, { useState } from 'react';
import { sha256 } from 'js-sha256';
import API_URL from '../assets/links';
import HEADERS from '../assets/headers';
import { useNavigate } from 'react-router-dom';
import '../components/component.css/Register.css'; // Import CSS
import memoriesIcon from '../assets/Icon/memories_icon.png';

function Register() {
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const navigate = useNavigate();

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError('');

    if (!username || !email || !password) {
      setError('All fields are required');
      return;
    }

    if (password.length < 8) {
      setError('Password must be at least 8 characters');
      return;
    }

    try {
      const response = await fetch(API_URL + 'v0.1/register.php', {
        method: 'POST',
        headers: HEADERS,
        body: JSON.stringify({ username, email, password: sha256(password) })
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || 'Registration failed');
      }

      setSuccess('Registration successful! Please login');
      navigate('/');

    } catch (error) {
      console.error('Registration error:', error);
      setError(error.message || 'Registration failed');
    }
  };

  return (
    <div className="register-page">
      <div className="register-container">
        <img src={memoriesIcon} alt="Logo" className="logo" />
        <h1 className="website-title">Sign Up</h1>
        {error && <p className="error-message">{error}</p>}
        {success && <p className="success-message">{success}</p>}
        <form onSubmit={handleSubmit}>
          <input
            type="text"
            placeholder="Username"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            required
          />
          <input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
          <input
            type="password"
            placeholder="Password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
          <button type="submit">Register</button>
        </form>
      </div>
    </div>
  );
}

export default Register;
