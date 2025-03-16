export const validatePasswordComplexity = (password) => {
  const hasNumber = /\d/.test(password);
  const hasSpecial = /[!@#$%^&*]/.test(password);
  return password.length >= 8 && hasNumber && hasSpecial;
};