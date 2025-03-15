import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import API from "../assets/api";
import "./component.css/Gallery.css";

const Skeleton = ({ height, className }) => (
  <div
    className={`bg-gray-200 animate-pulse rounded-lg ${className}`}
    style={{ height }}
  />
);

const GalleryComponent = () => {
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedTag, setSelectedTag] = useState(null);
  const [galleryData, setGalleryData] = useState({ tags: [], images: [] });
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);

  // Handle sidebar state and body class
  useEffect(() => {
    const body = document.body;
    if (isSidebarOpen && window.innerWidth <= 1024) {
      body.classList.add('sidebar-open');
    } else {
      body.classList.remove('sidebar-open');
    }

    return () => body.classList.remove('sidebar-open');
  }, [isSidebarOpen]);

  // Fetch gallery data
  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        // Token is handled by API service

        const params = new URLSearchParams({
          search: searchQuery,
          tag: selectedTag || ""
        });

        const response = await API.getPhotos(params);
        
        setGalleryData({
          tags: response.tags,
          images: response.data.filter(img => img.image_url),
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

  // Gallery Image Component
  const GalleryImage = ({ image }) => {
    const [loadError, setLoadError] = useState(false);

    const handleEditClick = (e) => {
      e.stopPropagation();
      navigate(`/update/${image.image_id}`, {
        state: {
          initialData: {
            title: image.title,
            date: image.date,
            description: image.description,
            tag: image.tag_name,
            image_url: image.image_url
          }
        }
      });
    };

    return (
      <div 
        className="gallery-image-container"
        onClick={() => navigate(`/photos/${image.image_id}`)}
      >
        {image.tag_name && (
          <h4 className="tag-name-header">{image.tag_name}</h4>
        )}
        <img
          src={`${API.BASE_URL}${image.image_url}`}
          alt={image.title}
          className="gallery-image"
          onError={() => setLoadError(true)}
        />

        <div className="image-overlay">
          <div className="image-info">
            <h3>{image.title ?? "Untitled"}</h3>
            <p>{image.description ?? "No description"}</p>
            <time>
              {image.date
                ? new Date(image.date).toLocaleDateString()
                : "Unknown Date"}
            </time>
            <button 
              className="edit-button"
              onClick={handleEditClick}
            >
              Edit
            </button>
          </div>
        </div>
        
        {loadError && (
          <div className="image-error">
            <span>Failed to load image</span>
          </div>
        )}
      </div>
    );
  };

  // Toggle sidebar visibility
  const toggleSidebar = () => {
    setIsSidebarOpen(!isSidebarOpen);
  };

  const selectedTagName = selectedTag
    ? galleryData.tags.find((t) => t.tag_id === selectedTag)?.tag_name
    : "All Photos";

  return (
    <div className="gallery-container">
      {loading && <p className="loading-message">Loading...</p>}
      {error && <p className="error-message">{error}</p>}
      
      <div className="gallery-layout">
        {/* Sidebar */}
        <div className={`gallery-sidebar ${isSidebarOpen ? "" : "collapsed"}`}>
          <button 
            className="sidebar-toggle"
            onClick={toggleSidebar}
          >
            {isSidebarOpen ? "⬅" : "➡"}
          </button>

          {isSidebarOpen && (
            <>
              <div className="sidebar-header">
                <h2>Memories Cherished</h2>
                <p className="pic-count">({galleryData.images.length}) pics</p>
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
                  Array(3)
                    .fill()
                    .map((_, i) => (
                      <Skeleton key={i} height={40} className="w-full" />
                    ))
                ) : error ? (
                  <div className="error-message">{error}</div>
                ) : (
                  galleryData.tags.map((tag) => (
                    <div
                      key={tag.tag_id}
                      className={`tag-item ${
                        selectedTag === tag.tag_id ? "tag-selected" : ""
                      }`}
                      onClick={() =>
                        setSelectedTag(
                          tag.tag_id === selectedTag ? null : tag.tag_id
                        )
                      }
                    >
                      <span>{tag.tag_name}</span>
                    </div>
                  ))
                )}
              </div>
            </>
          )}
        </div>

        {/* Main Content */}
        <div className="gallery-content">
          {/* Tag Section Headers */}
          {(galleryData.tags.length > 0 || galleryData.images.length > 0) && (
            <h1 className="tag-section-header">{selectedTagName}</h1>
          )}

          {/* Images Grid */}
          <div className="images-grid">
            {loading ? (
              Array(12)
                .fill()
                .map((_, i) => <Skeleton key={i} className="image-skeleton" />)
            ) : error ? (
              <div className="centered-message error-message">{error}</div>
            ) : galleryData.images.length === 0 ? (
              <div className="centered-message">
                No photos found for your search.
              </div>
            ) : (
              galleryData.images.map((image) => (
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