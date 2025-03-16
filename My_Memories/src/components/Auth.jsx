import React, { useEffect, useState } from 'react';
import { useNavigate, Outlet } from 'react-router-dom';
import API from '../services/API';

const Auth = () => {
  const navigate = useNavigate();
  const [isValid, setIsValid] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const validateToken = async () => {
      const token = localStorage.getItem('jwt_token');
      
      if (!token) {
        handleInvalidToken();
        return;
      }

      try {
        // Verify token with backend
        await API.verifyToken();
        
        // Additional client-side check
        const isValidToken = API.isTokenValid(token);
        
        if (!isValidToken) {
          throw new Error('Token expired');
        }

        setIsValid(true);
      } catch (error) {
        handleInvalidToken(error.message);
      } finally {
        setLoading(false);
      }
    };

    validateToken();
  }, [navigate]);

  const handleInvalidToken = (message = '') => {
    console.error('Authentication error:', message);
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('user');
    navigate('/');
  };

  if (loading) {
    return <div className="auth-loading">Checking authentication status...</div>;
  }

  return isValid ? <Outlet /> : <div className="auth-error">Authentication failed. Redirecting...</div>;
};

export default Auth;