export default class Photo {
  constructor({ imageId, ownerId, imageUrl, title, date, imageDescription }) {
    this.imageId = imageId;
    this.ownerId = ownerId;
    this.imageUrl = imageUrl;
    this.title = title;
    this.date = new Date(date); // Ensure date is a Date object
    this.imageDescription = imageDescription;
  }

  static validateImageUrl(imageUrl) {
    return /^https?:\/\/.+$/.test(imageUrl) && imageUrl.length <= 255;
  }

  static validateImageDescription(imageDescription) {
    return imageDescription.length <= 1000; // Increased length for TEXT type
  }

  static validateTitle(title) {
    return title.length <= 100;
  }
}