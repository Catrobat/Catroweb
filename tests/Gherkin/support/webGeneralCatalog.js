const DATASET_BY_TAG = new Map([
  ['@dataset-minimal', 'minimal'],
  ['@dataset-homepage', 'homepage'],
  ['@dataset-language-switcher', 'language-switcher'],
  ['@dataset-statistics-footer', 'statistics-footer'],
])

const LANGUAGE_COOKIE_BY_NAME = new Map([
  ['English', 'en'],
  ['Deutsch', 'de_DE'],
  ['German', 'de_DE'],
  ['Russisch', 'ru_RU'],
  ['Russian', 'ru_RU'],
  ['French', 'fr_FR'],
])

const PAGE_PATH_BY_NAME = new Map([
  ['homepage', '/'],
  ['login', '/app/login'],
  ['register', '/app/register'],
  ['project details', '/app/project/9002'],
  ['profile', '/app/user/9001'],
  ['luna landing page', '/luna'],
])

const SECTION_SELECTOR_BY_NAME = new Map([
  ['scratch remixes', '#home-projects__scratch'],
  ['most downloaded', '#home-projects__most_downloaded'],
])

function getDatasetFromTags(tags) {
  for (const tag of tags) {
    const dataset = DATASET_BY_TAG.get(tag)
    if (dataset) {
      return dataset
    }
  }

  return null
}

function getLanguageCookie(languageName) {
  const languageCookie = LANGUAGE_COOKIE_BY_NAME.get(languageName)
  if (!languageCookie) {
    throw new Error(`Unknown language "${languageName}"`)
  }

  return languageCookie
}

function getPagePath(pageName) {
  const pagePath = PAGE_PATH_BY_NAME.get(pageName)
  if (!pagePath) {
    throw new Error(`Unknown page "${pageName}"`)
  }

  return pagePath
}

function getSectionSelector(sectionName) {
  const sectionSelector = SECTION_SELECTOR_BY_NAME.get(sectionName)
  if (!sectionSelector) {
    throw new Error(`Unknown section "${sectionName}"`)
  }

  return sectionSelector
}

function getTableTexts(dataTable) {
  return dataTable
    .raw()
    .flat()
    .map((value) => value.trim())
    .filter(Boolean)
}

module.exports = {
  getDatasetFromTags,
  getLanguageCookie,
  getPagePath,
  getSectionSelector,
  getTableTexts,
}
