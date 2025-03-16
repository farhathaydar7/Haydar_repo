import axios from 'axios';

const API = axios.create({
  baseURL: 'http://localhost:8000', // Hardcoded backend URL
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
});

// Request interceptor for JWT
API.interceptors.request.use(config => {
  const token = localStorage.getItem('jwt_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor for error handling
API.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('jwt_token');
      window.location = '/';
    }
    return Promise.reject(error);
  }
);

const decodeToken = (token) => {
  try {
    return JSON.parse(atob(token.split('.')[1]));
  } catch {
    return null;
  }
};

export const isTokenValid = (token) => {
  const decoded = decodeToken(token);
  return decoded?.exp > Date.now() / 1000;
};

export default {
  // Authentication
  login: (credentials) => API.post('/login', credentials),
  register: (userData) => API.post('/register', userData),
  verifyToken: () => API.get('/verify-token'),

  // Photos
  getPhotos: (filters) => API.get('/photos', { params: filters }),
  getPhoto: (id) => API.get(`/photos/${id}`),
  uploadPhoto: (photoData) => API.post('/photos', photoData),
  updatePhoto: (id, data) => API.put(`/photos/${id}`, data),
  deletePhoto: (id) => API.delete(`/photos/${id}`),

  // Tags
  getTags: () => API.get('/tags'),
  createTag: (tagData) => API.post('/tags', tagData),

  // Utility function for converting files to base64
  convertToBase64: (file) => new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  }),

  // New method: Get image as base64
  getImageAsBase64: async (imageUrl) => {
    try {
      const response = await API.get('/image-base64', {
        params: { url: imageUrl }
      });
      return response.data.base64;
    } catch (error) {
      throw new Error(error.response?.data?.error || 'Failed to fetch image as base64');
    }
  }
};