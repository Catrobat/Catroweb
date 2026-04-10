import { escapeAttr, escapeHtml } from '../Components/HtmlEscape'

export function achievementBadgeHtml(achievement, variant) {
  let bannerTextLengthClass = ''

  if (achievement.title.length > 18) {
    bannerTextLengthClass = ' achievement__badge__banner__text--very-long'
  } else if (achievement.title.length > 12) {
    bannerTextLengthClass = ' achievement__badge__banner__text--long'
  }

  return (
    '<svg class="achievement__badge__coin achievement__badge__coin--' +
    variant +
    '"' +
    ' width="100%" height="100%"' +
    ' data-src="' +
    escapeAttr(achievement.badge_svg_path) +
    '" data-unique-ids="disabled"></svg>' +
    '<svg class="achievement__badge__banner achievement__badge__banner--' +
    variant +
    '"' +
    ' width="100%" height="100%"' +
    ' style="color: ' +
    escapeAttr(achievement.banner_color) +
    '"' +
    ' data-src="' +
    escapeAttr(achievement.banner_svg_path) +
    '" data-unique-ids="disabled"></svg>' +
    '<div class="achievement__badge__banner__text achievement__badge__banner__text--' +
    variant +
    bannerTextLengthClass +
    '" title="' +
    escapeAttr(achievement.title) +
    '">' +
    '<span class="achievement__badge__banner__text-label">' +
    escapeHtml(achievement.title) +
    '</span>' +
    '</div>'
  )
}
