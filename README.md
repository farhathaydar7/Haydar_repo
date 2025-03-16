# MyMemories API Documentation
Project is work in progress , current state is unfinished and still under developpment
http://52.47.95.15/
A RESTful API for managing user photos, tags, and authentication using JWT. Built with PHP, PDO, and SOLID principles.

---

## Table of Contents
- [Features](#features)
- [Technologies](#technologies)
- [Getting Started](#getting-started)
- [API Endpoints](#api-endpoints)
- [Authentication](#authentication)
- [Error Handling](#error-handling)
- [Examples](#examples)
- [Contributing](#contributing)

---

## Features
- 🔐 JWT Authentication
- 📸 Photo Upload/Management
- 🏷️ Tag Creation & Filtering
- 🔍 Search Photos by Title/Description
- 🔄 CORS Support

---

## Technologies
- **PHP 7.4+**
- **MySQL/PDO** (Database)
- **Firebase JWT** (Token Authentication)
- **Dotenv** (Environment Configuration)

---

## Getting Started

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Composer

### Installation
1. Clone the repo:
   ```bash
   git clone https://github.com/farhathaydar7/Haydar_repo.git
   cd My_memories_server_revamped
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Edit `config.php` file:
  
   DB_HOST=localhost
   DB_NAME=mymemories
   DB_USER=root
   DB_PASS=
   JWT_SECRET=your_strong_secret_here
   UPLOAD_DIR=./public/uploads
   APP_ENV=development
    MIGRATION_KEY=YOUR_MIGRATION_KEY
  
4. Migrate using the migration key by calling the endpoint "http://localhost:8000/migration.php?api_key=YOUR_MIGRATION_KEY"

---

## API Endpoints

### Authentication
| Method | Endpoint    | Description          |
|--------|-------------|----------------------|
| POST   | `/login`    | User login           |
| POST   | `/register` | User registration    |

### Photos
| Method | Endpoint       | Description                          |
|--------|----------------|--------------------------------------|
| GET    | `/photos`      | Get filtered photos                  |
| GET    | `/photos/{id}` | Get single photo by ID               |
| POST   | `/photos`      | Upload new photo                     |
| PUT    | `/photos/{id}` | Update photo details                 |

### Tags
| Method | Endpoint | Description          |
|--------|----------|----------------------|
| GET    | `/tags`  | Get all user tags    |

---

## Authentication
All endpoints (except `/login` and `/register`) require a JWT token in the `Authorization` header.

**Example Request:**
```http
POST /login HTTP/1.1
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": { "id": 1, "email": "user@example.com" }
}
```

---

## API Details

### 1. Get Photos (Filtered)
**GET** `/photos?owner_id=1&tag=3&search=sunset`

**Response:**
```json
{
  "tags": [
    { "tag_id": 1, "tag_name": "Nature", "count": 5 }
  ],
  "images": [
    {
      "id": 1,
      "title": "Sunset",
      "image_url": "/uploads/1/img_123.jpg",
      "tag_name": "Nature"
    }
  ]
}
```

### 2. Upload Photo
**POST** `/photos`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Body:**
```json
{
  "title": "Mountain View",
  "image": "base64_encoded_data",
  "tag": "Nature"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "filePath": "/uploads/1/img_456.jpg"
}
```

---

## Error Handling
| Code | Message                      | Description                     |
|------|------------------------------|---------------------------------|
| 400  | Invalid JSON input           | Malformed request body          |
| 401  | Authorization header missing | JWT token not provided          |
| 403  | User ID mismatch             | Token doesn't match request     |
| 404  | Photo not found              | Invalid photo ID                |
| 500  | Database error               | Server-side issue               |

---

## Examples

### Upload Photo with cURL
```bash
curl -X POST http://localhost:8000/photos \
  -H "Authorization: Bearer eyJhbGciOiJIUz..." \
  -H "Content-Type: application/json" \
  -d '{"title": "Beach", "image": "base64...", "tag": "Vacation"}'
```

### Update Photo
```bash
curl -X PUT http://localhost:8000/photos/5 \
  -H "Authorization: Bearer eyJhbGciOiJIUz..." \
  -H "Content-Type: application/json" \
  -d '{"title": "New Title", "description": "Updated description"}'
```

---

## Contributing
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

**License**: Tottally legit MIT liscence 
**Maintainer**: Haidar Farhat 
**Version**: 2.0.0

