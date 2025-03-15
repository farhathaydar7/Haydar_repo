import API_URL from './links.jsx';

const API = {
  BASE_URL: API_URL,
  
  async request(method, endpoint, data = null) {
    const url = `${this.BASE_URL}${endpoint}`;
    const token = localStorage.getItem('jwt_token');
    
    const config = {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    };

    if (data) config.body = JSON.stringify(data);

    const response = await fetch(url, config);
    const responseData = await response.json();

    if (!response.ok) {
      throw new Error(responseData.error || 'API request failed');
    }

    return responseData;
  },

  getPhotos(params) {
    const query = new URLSearchParams(params).toString();
    return this.request('GET', `/photos?${query}`);
  },

  getPhotoDetails(id) {
    return this.request('GET', `/photos/${id}`);
  },

  uploadPhoto(data) {
    return this.request('POST', '/photos', data);
  },

  updatePhoto(id, data) {
    return this.request('PUT', `/photos/${id}`, data);
  },

  getTags() {
    return this.request('GET', '/tags');
  },

  async verifyToken(token) {
    const response = await fetch(`${this.BASE_URL}/verify-token`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    return response.ok;
  }
};

export default API;