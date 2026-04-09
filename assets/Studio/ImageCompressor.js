const MAX_FILE_SIZE = 1048576 // 1MB
const MAX_DIMENSION = 1200
const JPEG_QUALITY = 0.85
const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']

/**
 * Validates file type against allowed MIME types.
 *
 * @param {File} file
 * @returns {boolean}
 */
export function isAllowedImageType(file) {
  return ALLOWED_MIME_TYPES.includes(file.type)
}

/**
 * Returns whether the file exceeds the maximum upload size.
 *
 * @param {File} file
 * @returns {boolean}
 */
export function exceedsMaxSize(file) {
  return file.size > MAX_FILE_SIZE
}

/**
 * Compresses an image file using Canvas API if it exceeds the size limit.
 * Returns the original file if it is already within limits or is a GIF.
 *
 * @param {File} file - The image file to potentially compress
 * @returns {Promise<{file: File, wasCompressed: boolean}>}
 */
export async function compressImageIfNeeded(file) {
  if (file.size <= MAX_FILE_SIZE) {
    return { file, wasCompressed: false }
  }

  // GIFs cannot be meaningfully compressed via Canvas (loses animation)
  if (file.type === 'image/gif') {
    return { file, wasCompressed: false }
  }

  const compressed = await compressImage(file)
  return { file: compressed, wasCompressed: true }
}

/**
 * Compresses an image by resizing and converting to JPEG via Canvas API.
 *
 * @param {File} file
 * @returns {Promise<File>}
 */
function compressImage(file) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    const url = URL.createObjectURL(file)

    img.onload = () => {
      URL.revokeObjectURL(url)

      let { width, height } = img

      // Scale down if either dimension exceeds the max
      if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
        const ratio = Math.min(MAX_DIMENSION / width, MAX_DIMENSION / height)
        width = Math.round(width * ratio)
        height = Math.round(height * ratio)
      }

      const canvas = document.createElement('canvas')
      canvas.width = width
      canvas.height = height

      const ctx = canvas.getContext('2d')
      ctx.drawImage(img, 0, 0, width, height)

      canvas.toBlob(
        (blob) => {
          if (!blob) {
            reject(new Error('Canvas compression failed'))
            return
          }

          const compressedName = file.name.replace(/\.[^.]+$/, '.jpg')
          resolve(new File([blob], compressedName, { type: 'image/jpeg' }))
        },
        'image/jpeg',
        JPEG_QUALITY,
      )
    }

    img.onerror = () => {
      URL.revokeObjectURL(url)
      reject(new Error('Failed to load image for compression'))
    }

    img.src = url
  })
}
