import { useState } from 'react';

const useUser = () => {
  const [user, setUser] = useState({
    id: null,
    username: '',
    email: '',
    password: '',
  });

  const validateEmail = (email) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  };

  const validateUsername = (username) => {
    return username.length >= 3 && username.length <= 50;
  };

  const validatePasswordComplexity = (password) => {
    return password.length >= 8;
  };

  return {
    user,
    setUser,
    validateEmail,
    validateUsername,
    validatePasswordComplexity,
  };
};

export default useUser;