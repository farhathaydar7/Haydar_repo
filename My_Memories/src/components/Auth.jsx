import React, { useEffect, useState } from 'react';
import { useNavigate, Outlet } from 'react-router-dom';
import { jwtDecode } from 'jwt-decode';
import API from '../assets/api';

const Auth = () => {
  const navigate = useNavigate();
  const [isValid, setIsValid] = useState(false);

  useEffect(() => {
    const validateToken = async () => {
      const token = localStorage.getItem('jwt_token');
      
      if (!token) {
        navigate('/');
        return;
      }

      try {
        await API.verifyToken(token);
        const decoded = jwtDecode(token);
        
        if (decoded.exp < Date.now() / 1000) {
          throw new Error('Token expired');
        }
        
        setIsValid(true);
      // eslint-disable-next-line no-unused-vars
      } catch (error) {
        localStorage.removeItem('jwt_token');
        navigate('/');
      }
    };

    validateToken();
  }, [navigate]);

  return isValid ? <Outlet /> : <div className="auth-loading">Verifying authentication...</div>;
};

export default Auth;