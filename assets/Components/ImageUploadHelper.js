import {
  compressImageIfNeeded,
  exceedsMaxSize,
  isAllowedImageType,
} from '../Studio/ImageCompressor'

export const ImageUploadError = {
  invalidType: 'invalid-type',
  tooLarge: 'too-large',
  processingFailed: 'processing-failed',
}

export async function prepareImageFileForUpload(file) {
  if (!file || !isAllowedImageType(file)) {
    return { ok: false, error: ImageUploadError.invalidType }
  }

  try {
    const result = await compressImageIfNeeded(file)
    if (exceedsMaxSize(result.file)) {
      return { ok: false, error: ImageUploadError.tooLarge }
    }

    return { ok: true, file: result.file, wasCompressed: result.wasCompressed }
  } catch {
    return { ok: false, error: ImageUploadError.processingFailed }
  }
}

export function readFileAsDataUrl(file) {
  return new Promise((resolve, reject) => {
    const reader = new window.FileReader()
    reader.onerror = () => reject(new Error('Failed to read image file'))
    reader.onload = (event) => resolve(event.currentTarget.result)
    reader.readAsDataURL(file)
  })
}
