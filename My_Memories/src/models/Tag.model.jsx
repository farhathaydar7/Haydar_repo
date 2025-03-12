export default class Tag {
  constructor({ tagId, tagName, tagOwner }) {
    this.tagId = tagId;
    this.tagName = tagName;
    this.tagOwner = tagOwner;
  }

  static validateTagName(tagName) {
    return tagName.length >= 2 && tagName.length <= 50;
  }
}