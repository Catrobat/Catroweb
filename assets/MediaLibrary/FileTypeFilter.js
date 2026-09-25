const FILE_TYPES = ['IMAGE', 'SOUND']

/**
 * The file type the page was opened for (`?file_type=IMAGE|SOUND`). The Catroid app opens the
 * library this way from its looks and sounds screens, so only matching assets may be offered.
 */
export function getFileTypeFilter() {
  const fileType = (
    new URLSearchParams(window.location.search).get('file_type') || ''
  ).toUpperCase()
  return FILE_TYPES.includes(fileType) ? fileType : null
}
