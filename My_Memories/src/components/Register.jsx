import React, { useState } from 'react';
import { sha256 } from 'js-sha256';
import API_URL from '../assets/links';
import HEADERS from '../assets/headers';

function Register() {
  const [username, setUsername] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError('');
    setSuccess('');
    try {
      const response = await fetch(API_URL + 'v0.1/register.php', { // Adjust path if necessary
        method: 'POST',
        headers: HEADERS,
        body: JSON.stringify({ username, email, password: sha256(password) }),
      });

      const data = await response.json();

      if (response.ok && data.message === 'User registered successfully') {
        setSuccess(data.message);
        // Optionally redirect to login page after successful registration
      } else {
        setError(data.message || 'Registration failed');
      }
    } catch (e) {
      setError('Failed to connect to server');
      console.error("Registration error:", e);
    }
  };

  return (
    <div>
      <h2>Register</h2>
      {error && <p style={{ color: 'red' }}>{error}</p>}
      {success && <p style={{ color: 'green' }}>{success}</p>}
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
          <label htmlFor="email">Email:</label>
          <input
            type="email"
            id="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
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
        <button type="submit">Register</button>
      </form>
    </div>
  );
}

export default Register;