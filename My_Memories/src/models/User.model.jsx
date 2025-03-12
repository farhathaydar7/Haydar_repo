export default class User {
  constructor({ id, username, email, password }) {
    this.id = id;
    this.username = username;
    this.email = email;
    this.password = password;
  }

  validatePassword(password) {
    return this.password === password; // Consider hashing later
  }

  static validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) && email.length <= 100; // Added length validation
  }

  static validateUsername(username) {
    return username.length >= 3 && username.length <= 50; // Adjusted max length to 50
  }

  static validatePasswordComplexity(password) {
    return password.length >= 8;
  }
}