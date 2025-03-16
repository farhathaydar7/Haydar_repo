import React, { useState } from 'react';
import API from '../services/API';
import { useNavigate } from 'react-router-dom';
import '../components/component.css/Register.css'; // Import CSS
import memoriesIcon from '../assets/Icon/memories_icon.png';

function Register() {
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
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
      await API.register({
        username,
        email: email.trim().toLowerCase(),
        password
      });

      // Auto-login after registration
      const loginResponse = await API.login({
        email: email.trim().toLowerCase(),
        password
      });

      localStorage.setItem('jwt_token', loginResponse.data.token);
      localStorage.setItem('user', JSON.stringify(loginResponse.data.user));
      navigate('/gallery');

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
