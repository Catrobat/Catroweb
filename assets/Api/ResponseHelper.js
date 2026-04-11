/**
 * Normalize an API response into envelope format.
 * Handles backward compatibility with raw array responses.
 */
export function normalizeApiResponse(response) {
  return Array.isArray(response) ? { data: response } : response
}

/**
 * Extract field-level validation errors from a unified ErrorResponse.
 * Returns an object mapping field names to error messages.
 * Falls back to returning the original object if not in the expected format.
 */
export function extractFieldErrors(responseObj) {
  const details = responseObj?.error?.details
  if (!Array.isArray(details)) return responseObj
  const byField = {}
  details.forEach((d) => {
    byField[d.field] = d.message
  })
  return byField
}
