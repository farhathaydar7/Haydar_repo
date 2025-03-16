import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import API from "../services/API";
import "./component.css/Gallery.css";

// Skeleton loading component
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
  const [allImages, setAllImages] = useState([]);
  const [filteredImages, setFilteredImages] = useState([]);
  const [tags, setTags] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);

  // Handle sidebar state and body class
  useEffect(() => {
    const body = document.body;
    if (isSidebarOpen && window.innerWidth <= 1024) {
      body.classList.add("sidebar-open");
    } else {
      body.classList.remove("sidebar-open");
    }
    return () => body.classList.remove("sidebar-open");
  }, [isSidebarOpen]);

  // Fetch initial data using the Base64 images already provided in the response
  useEffect(() => {
    const fetchInitialData = async () => {
      try {
        const response = await API.getPhotos({ search: "", tag: "" });
        // Note the change here: accessing the nested data
        const images = response.data?.data?.images || [];
        const tags = response.data?.data?.tags || [];
        
        setAllImages(images);
        setFilteredImages(images);
        setTags(tags);
      } catch (err) {
        setError(err.response?.data?.error || err.message);
      } finally {
        setLoading(false);
      }
    };
    fetchInitialData();
  }, []);

  // Filter images when search or tag changes
  useEffect(() => {
    let filtered = allImages;
    if (searchQuery) {
      const query = searchQuery.toLowerCase();
      filtered = filtered.filter(img =>
        (img.title || "").toLowerCase().includes(query) ||
        (img.description || "").toLowerCase().includes(query)
      );
    }
    if (selectedTag) {
      // Convert img.tag_id to a number for comparison.
      filtered = filtered.filter(img => Number(img.tag_id) === selectedTag);
    }
    setFilteredImages(filtered);
  }, [searchQuery, selectedTag, allImages]);

  // Gallery Image Component
  const GalleryImage = ({ image }) => {
    const [loadError, setLoadError] = useState(false);
    // Use image_base64 if available; otherwise, fallback to constructed URL.
    const imageUrl = image.image_base64 || `http://localhost:8000/${image.image_url}`;
    const tag = tags.find((t) => t.tag_id === image.tag_id)?.tag_name || "No Tag";

    const handleEditClick = (e) => {
      e.stopPropagation();
      navigate(`/update/${image.image_id}`, {
        state: {
          initialData: {
            title: image.title,
            date: image.date,
            description: image.description,
            tag,
            image_url: image.image_url,
          },
        },
      });
    };

    return (
      <div
        className="gallery-image-container"
        onClick={() => navigate(`/photos/${image.image_id}`)}
      >
        <h4 className="tag-name-header">{tag}</h4>
        {!loadError ? (
          <img
            src={imageUrl}
            alt={image.title}
            className="gallery-image"
            onError={() => setLoadError(true)}
          />
        ) : (
          <div className="image-error">Failed to load image</div>
        )}
        <div className="image-overlay">
          <div className="image-info">
            <h3>{image.title ?? "Untitled"}</h3>
            <p>{image.description ?? "No description"}</p>
            <time>
              {image.date ? new Date(image.date).toLocaleDateString() : "Unknown Date"}
            </time>
            <button
              className="edit-button"
              onClick={handleEditClick}
              onMouseDown={(e) => e.stopPropagation()}
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
    ? tags.find((t) => Number(t.tag_id) === selectedTag)?.tag_name || "All Photos"
    : "All Photos";

  return (
    <div className="gallery-container">
      {loading && <p className="loading-message">Loading...</p>}
      {error && <p className="error-message">{error}</p>}
      
      <div className="gallery-layout">
        {/* Sidebar */}
        <div className={`gallery-sidebar ${isSidebarOpen ? "" : "collapsed"}`}>
          <button className="sidebar-toggle" onClick={toggleSidebar}>
            {isSidebarOpen ? "⬅" : "➡"}
          </button>
          {isSidebarOpen && (
            <>
              <div className="sidebar-header">
                <h2>Memories Cherished</h2>
                <p className="pic-count">({filteredImages.length}) pics</p>
              </div>
              <div className="search-bar">
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="Search images..."
                />
                <select
                  value={selectedTag || ""}
                  onChange={(e) => setSelectedTag(Number(e.target.value) || null)}
                >
                  <option value="">All Tags</option>
                  {tags.map((tag) => (
                    <option key={tag.tag_id} value={tag.tag_id}>
                      {tag.tag_name}
                    </option>
                  ))}
                </select>
              </div>
            </>
          )}
        </div>

        {/* Main Content */}
        <div className="gallery-content">
          {(tags.length > 0 || filteredImages.length > 0) && (
            <h1 className="tag-section-header">{selectedTagName}</h1>
          )}
          <div className="images-grid">
            {loading ? (
              Array(12)
                .fill()
                .map((_, i) => <Skeleton key={i} className="image-skeleton" />)
            ) : error ? (
              <div className="centered-message error-message">{error}</div>
            ) : filteredImages.length === 0 ? (
              <div className="centered-message">No photos found for your search.</div>
            ) : (
              filteredImages.map((image) => (
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
