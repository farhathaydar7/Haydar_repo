import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import API from '../assets/api';
import { TextField, Button, Select, MenuItem, FormControl, InputLabel } from '@mui/material';

const UpdateImage = () => {
  const { photoId } = useParams();
  const navigate = useNavigate();
  const [tags, setTags] = useState([]);
  const [formData, setFormData] = useState({
    title: '',
    date: '',
    description: '',
    tag: ''
  });
  const [error, setError] = useState('');

  // Fetch existing photo data and tags
  useEffect(() => {
    const fetchData = async () => {
      try {
        const [photoResponse, tagsResponse] = await Promise.all([
          API.getPhotoDetails(photoId),
          API.getTags()
        ]);
        
        setFormData({
          title: photoResponse.data.title,
          date: photoResponse.data.date.split('T')[0],
          description: photoResponse.data.description,
          tag: photoResponse.data.tag_id
        });
        
        setTags(tagsResponse.data);
      } catch (err) {
        setError(err.message);
      }
    };
    
    fetchData();
  }, [photoId]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await API.updatePhoto(photoId, {
        ...formData,
        tag_id: formData.tag
      });
      navigate(`/photos/${photoId}`);
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <div className="update-container">
      <h2>Edit Memory</h2>
      {error && <div className="error-message">{error}</div>}
      
      <form onSubmit={handleSubmit}>
        <TextField
          label="Title"
          fullWidth
          margin="normal"
          value={formData.title}
          onChange={(e) => setFormData({...formData, title: e.target.value})}
        />
        
        <TextField
          label="Date"
          type="date"
          fullWidth
          margin="normal"
          InputLabelProps={{ shrink: true }}
          value={formData.date}
          onChange={(e) => setFormData({...formData, date: e.target.value})}
        />
        
        <TextField
          label="Description"
          multiline
          rows={4}
          fullWidth
          margin="normal"
          value={formData.description}
          onChange={(e) => setFormData({...formData, description: e.target.value})}
        />
        
        <FormControl fullWidth margin="normal">
          <InputLabel>Tag</InputLabel>
          <Select
            value={formData.tag}
            onChange={(e) => setFormData({...formData, tag: e.target.value})}
          >
            {tags.map((tag) => (
              <MenuItem key={tag.tag_id} value={tag.tag_id}>
                {tag.tag_name}
              </MenuItem>
            ))}
          </Select>
        </FormControl>
        
        <Button 
          type="submit" 
          variant="contained" 
          color="primary"
          fullWidth
        >
          Update Memory
        </Button>
      </form>
    </div>
  );
};

export default UpdateImage;