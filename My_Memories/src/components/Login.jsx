import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import API_URL from '../assets/links';
import HEADERS from '../assets/headers';

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
                console.error("JSON parse error:", e);
                return;
              }

      if (response.ok && data.token) {
        // Store the token
        localStorage.setItem('jwt_token', data.token);
        alert('Login successful!');
        // Redirect to gallery
      } else {
        setError(data.error || 'Login failed');
      }
    } catch (e) {
      setError('Failed to connect to server');
      console.error("Login error:", e);
    }
  };

  return (
    <div>
      <h2>Login</h2>
      {error && <p style={{ color: 'red' }}>{error}</p>}
      <form onSubmit={handleSubmit}>
        <div>
          <label htmlFor="username">Username:</label>
          <input
            type="text"
            id="username"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            required
          />
        </div>
        <div>
          <label htmlFor="password">Password:</label>
          <input
            type="password"
            id="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        </div>
        <button type="submit">Login</button>
      </form>
      <button
        type="button"
        onClick={() => navigate('/register')}
        style={{ marginTop: '1rem' }}
      >
        Don't have an account? Sign up
      </button>
    </div>
  );
}

export default Login;