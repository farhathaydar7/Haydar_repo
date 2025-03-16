import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { TextField, Button, Select, MenuItem, FormControl, InputLabel, Box, Typography } from '@mui/material';
import API from '../services/API';
import usePhoto from '../models/Photo.model';
import useTag from '../models/Tag.model';

const UpdateImage = () => {
  const { photoId } = useParams();
  const navigate = useNavigate();
  const { photo, setPhoto, validateDescription } = usePhoto();
  const { tags, setTags, validateName } = useTag();
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [photoResponse, tagsResponse] = await Promise.all([
          API.getPhoto(photoId),
          API.getTags()
        ]);

        setPhoto({
          title: photoResponse.data.title,
          date: photoResponse.data.date.split('T')[0],
          description: photoResponse.data.description,
          tag_id: photoResponse.data.tag_id,
          image_url: photoResponse.data.image_url
        });

        setTags(tagsResponse.data);
      } catch (error) {
        setError(error.response?.data?.error || error.message);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [photoId, setPhoto, setTags]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      // Validate inputs
      if (!validateDescription(photo.description)) {
        throw new Error('Description must be less than 500 characters');
      }

      if (photo.tag_id && !validateName(tags.find(t => t.tag_id === photo.tag_id)?.tag_name || '')) {
        throw new Error('Invalid tag name');
      }

      await API.updatePhoto(photoId, {
        title: photo.title,
        date: photo.date,
        description: photo.description,
        tag_id: photo.tag_id
      });

      navigate(`/photos/${photoId}`);
    } catch (error) {
      setError(error.response?.data?.error || error.message);
    }
  };

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" mt={4}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box sx={{ maxWidth: 800, mx: 'auto', p: 3 }}>
      <Typography variant="h4" gutterBottom>
        Edit Memory
      </Typography>

      {error && (
        <Typography color="error" sx={{ mb: 2 }}>
          {error}
        </Typography>
      )}

      <Box
        component="form"
        onSubmit={handleSubmit}
        sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}
      >
        <TextField
          label="Title"
          fullWidth
          value={photo.title}
          onChange={(e) => setPhoto({ ...photo, title: e.target.value })}
          required
        />

        <TextField
          label="Date"
          type="date"
          fullWidth
          InputLabelProps={{ shrink: true }}
          value={photo.date}
          onChange={(e) => setPhoto({ ...photo, date: e.target.value })}
          required
        />

        <TextField
          label="Description"
          multiline
          rows={4}
          fullWidth
          value={photo.description}
          onChange={(e) => setPhoto({ ...photo, description: e.target.value })}
          error={!validateDescription(photo.description)}
          helperText={
            !validateDescription(photo.description) &&
            'Description must be less than 500 characters'
          }
        />

        <FormControl fullWidth>
          <InputLabel>Tag</InputLabel>
          <Select
            value={photo.tag_id || ''}
            label="Tag"
            onChange={(e) => setPhoto({ ...photo, tag_id: e.target.value })}
          >
            <MenuItem value="">No Tag</MenuItem>
            {tags.map((tag) => (
              <MenuItem key={tag.tag_id} value={tag.tag_id}>
                {tag.tag_name}
              </MenuItem>
            ))}
          </Select>
        </FormControl>

        <Box sx={{ display: 'flex', gap: 2, mt: 2 }}>
          <Button
            variant="contained"
            type="submit"
            sx={{ flex: 1 }}
          >
            Save Changes
          </Button>

          <Button
            variant="outlined"
            onClick={() => navigate(`/photos/${photoId}`)}
            sx={{ flex: 1 }}
          >
            Cancel
          </Button>
        </Box>
      </Box>

      <Box sx={{ mt: 4, textAlign: 'center' }}>
        <img
          src={`${API.BASE_URL}${photo.image_url}`}
          alt={photo.title}
          style={{
            maxWidth: '100%',
            maxHeight: '400px',
            borderRadius: '8px',
            boxShadow: '0 4px 8px rgba(0,0,0,0.1)'
          }}
        />
      </Box>
    </Box>
  );
};

export default UpdateImage;