import React, { useState, useEffect } from 'react';
import API_URL from '../assets/links.jsx';
import './component.css/Gallery.css';
const Skeleton = ({ height, className }) => (
  <div className={`bg-gray-200 animate-pulse rounded-lg ${className}`} style={{ height }} />
);

const GalleryComponent = () => {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedTag, setSelectedTag] = useState(null);
  const [galleryData, setGalleryData] = useState({ tags: [], images: [] });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const token = localStorage.getItem('jwt_token');
        const user = localStorage.getItem('user');
        let owner_id = null;
        if (user) {
          try {
            const userData = JSON.parse(user);
            owner_id = userData.id;
          } catch (e) {
            console.error('Failed to parse user data:', e);
          }
        }
        const params = new URLSearchParams({
          owner_id: owner_id,
          search: searchQuery,
          tag: selectedTag || '',
        });

        const response = await fetch(`${API_URL}v0.1/fill_gallery.php?${params}`, {
          headers: { 'Authorization': `Bearer ${token}` },
        });

        if (!response.ok) throw new Error('Failed to fetch data');
        const data = await response.json();

        setGalleryData({
          tags: data.tags,
          images: data.images.filter(img => img.image_data),
        });
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    const debounceTimer = setTimeout(fetchData, 300);
    return () => clearTimeout(debounceTimer);
  }, [searchQuery, selectedTag]);

  const GalleryImage = ({ image }) => {
    const [loadError, setLoadError] = useState(false);
    if (!image.image_data || !image.mime_type) return null;

    return (
      <div className="gallery-image-container">
        {/* Display tag name above the image */}
        {image.tag_name && <h4 className="tag-name-header">{image.tag_name}</h4>}
        <img
          src={`data:${image.mime_type};base64,${image.image_data}`}
          alt={image.title}
          className="gallery-image"
          onLoad={(e) => {
            if (e.target.naturalWidth === 0) {
              setLoadError(true);
            }
          }}
          onError={() => setLoadError(true)}
        />

        {loadError && (
          <div className="image-error">
            <span>Failed to load image</span>
          </div>
        )}
        <div className="image-overlay">
          <div className="image-info">
            <h3>{image.title ?? "Untitled"}</h3>
            <p>{image.description ?? "No description"}</p>
            <time>
              {image.date ? new Date(image.date).toLocaleDateString() : "Unknown Date"}
            </time>
          </div>
        </div>
      </div>
    );
  };

  const selectedTagName = selectedTag
    ? galleryData.tags.find(t => t.tag_id === selectedTag)?.tag_name
    : 'All Photos';

  return (
    <div className="gallery-container">
      {loading && <p className="loading-message">Loading...</p>}
      {error && <p className="error-message">{error}</p>}
      <div className="gallery-layout">
        {/* Sidebar */}
        {galleryData.tags && galleryData.tags.length > 0 && (
          <div className="gallery-sidebar">
            <div className="sidebar-header">
              <h2>Name</h2>
              <p className="pic-count"># of pics</p>
            </div>
            <div className="search-container">
              <input
                type="text"
                placeholder="Search"
                className="search-input"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
              />
            </div>

            <h2 className="tags-header">Tags</h2>
            <div className="tags-list">
              {loading ? (
                Array(3).fill().map((_, i) => <Skeleton key={i} height={40} className="w-full" />)
              ) : error ? (
                <div className="error-message">{error}</div>
              ) : (
                galleryData.tags.map(tag => (
                  <div
                    key={tag.tag_id}
                    className={`tag-item ${selectedTag === tag.tag_id ? 'tag-selected' : ''}`}
                    onClick={() => setSelectedTag(tag.tag_id === selectedTag ? null : tag.tag_id)}
                  >
                    <span>{tag.tag_name}</span>
                  </div>
                ))
              )}
            </div>
          </div>
        )}

        {/* Main Content */}
        <div className="gallery-content">
          {/* Tag Section Headers */}
          {(galleryData.tags.length > 0 || galleryData.images.length > 0) && (
            <h1 className="tag-section-header">
              {selectedTagName}
            </h1>
          )}

          {/* Images Grid */}
          <div className="images-grid">
            {loading ? (
              Array(12).fill().map((_, i) => <Skeleton key={i} className="image-skeleton" />)
            ) : error ? (
              <div className="centered-message error-message">{error}</div>
            ) : galleryData.images.length === 0 ? (
              <div className="centered-message">
                No photos found {searchQuery ? `for "{searchQuery}"` : ''}
              </div>
            ) : (
              galleryData.images.map(image => (
                <GalleryImage key={image.image_id} image={image} />
              ))
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default GalleryComponent;