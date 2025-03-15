import API_URL from "./links";
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
  
      if (data) {
        config.body = JSON.stringify(data);
      }
  
      try {
        const response = await fetch(url, config);
        
        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
        }
  
        return await response.json();
      } catch (error) {
        console.error('API request failed:', error);
        throw error;
      }
    },
  
    // Photo Endpoints
    getPhotos(params = {}) {
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
  
    // Tag Endpoints
    getTags() {
      return this.request('GET', '/tags');
    },
  
    // Auth Endpoints
    async verifyToken() {
      return this.request('GET', '/verify-token');
    }
  };
  
  export default API;