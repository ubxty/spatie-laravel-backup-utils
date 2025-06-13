# Open Graph Image Generation

This directory contains tools for generating Open Graph images for the documentation site.

## Method 1: Using the HTML Generator

1. Open `og-image-generator.html` in a browser
2. Take a screenshot at exactly 1200x630 pixels
3. Save as `og-image.png`

### Page-specific variations:

**Homepage:**
```javascript
generateVariation('Spatie Laravel<br>Backup Utils', 'Enhanced CLI Tools & Monitoring', 'Professional backup management extensions for Laravel applications');
```

**Installation:**
```javascript
generateVariation('Installation<br>Guide', 'Easy Setup Process', 'Get started with Spatie Laravel Backup Utils in minutes');
```

**Configuration:**
```javascript
generateVariation('Configuration<br>Guide', 'Customize Your Setup', 'Advanced configuration options for backup management');
```

**Usage:**
```javascript
generateVariation('Usage<br>Guide', 'CLI Commands & Examples', 'Learn how to use backup utilities effectively');
```

## Method 2: Using Online Tools

1. Visit [OG Image Generator](https://og-playground.vercel.app/)
2. Use the following template:
   - Title: "Spatie Laravel Backup Utils by UBXTY"
   - Description: "Enhanced CLI tools and monitoring for Laravel backups"
   - Background: Linear gradient #4f46e5 to #7c3aed
   - Font: Inter or System fonts

## Method 3: Automated Generation (Future)

Consider using tools like:
- Puppeteer for automated screenshots
- Canvas API for dynamic generation
- Vercel OG Image Generation
- Cloudinary for dynamic images

## Current Images Needed

- `og-image.png` - Default/Homepage image (1200x630)
- `og-image-installation.png` - Installation page (1200x630)
- `og-image-configuration.png` - Configuration page (1200x630)
- `og-image-usage.png` - Usage page (1200x630)

## Image Specifications

- **Size:** 1200x630 pixels (Facebook/Twitter recommended)
- **Format:** PNG or JPG
- **File size:** Under 8MB (preferably under 1MB)
- **Aspect ratio:** 1.91:1 