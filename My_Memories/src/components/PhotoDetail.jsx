import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Button, Typography, Box, CircularProgress } from '@mui/material';
import API from '../services/API';
import usePhoto from '../models/Photo.model';

const PhotoDetail = () => {
  const { photoId } = useParams();
  const navigate = useNavigate();
  const { photo, setPhoto } = usePhoto();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchPhoto = async () => {
      try {
        const response = await API.getPhoto(photoId);
        setPhoto({
          image_id: response.data.image_id,
          title: response.data.title,
          date: response.data.date,
          description: response.data.description,
          image_url: response.data.image_url,
          tag_id: response.data.tag_id,
          tag_name: response.data.tag_name
        });
      } catch (error) {
        setError(error.response?.data?.error || error.message);
      } finally {
        setLoading(false);
      }
    };

    fetchPhoto();
  }, [photoId, setPhoto]);

  const handleEdit = () => {
    navigate(`/update/${photoId}`, {
      state: {
        initialData: {
          title: photo.title,
          date: photo.date,
          description: photo.description,
          tag: photo.tag_name,
          image_url: photo.image_url
        }
      }
    });
  };

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" mt={4}>
        <CircularProgress />
      </Box>
    );
  }

  if (error) {
    return (
      <Box p={4}>
        <Typography color="error">{error}</Typography>
      </Box>
    );
  }

  return (
    <Box sx={{ maxWidth: 800, mx: 'auto', p: 3 }}>
      <Button 
        variant="contained" 
        onClick={handleEdit}
        sx={{ mb: 3 }}
      >
        Edit Memory
      </Button>

      <Typography variant="h3" gutterBottom>
        {photo.title || 'Untitled Memory'}
      </Typography>

      <Box sx={{ mb: 3 }}>
        <img
          src={`${API.BASE_URL}${photo.image_url}`}
          alt={photo.title}
          style={{ 
            maxWidth: '100%', 
            borderRadius: 8,
            boxShadow: '0 4px 8px rgba(0,0,0,0.1)'
          }}
        />
      </Box>

      <Box sx={{ mb: 2 }}>
        <Typography variant="subtitle1" color="textSecondary">
          Date: {new Date(photo.date).toLocaleDateString()}
        </Typography>
        <Typography variant="subtitle1" color="textSecondary">
          Tag: {photo.tag_name || 'No tag'}
        </Typography>
      </Box>

      <Typography 
        variant="body1" 
        paragraph
        sx={{
          whiteSpace: 'pre-wrap',
          lineHeight: 1.6,
          fontSize: '1.1rem'
        }}
      >
        {photo.description || 'No description available'}
      </Typography>
    </Box>
  );
};

export default PhotoDetail;