package org.catrobat.web.catroid;

import com.badlogic.gdx.scenes.scene2d.Action;

import org.catrobat.catroid.content.Scope;
import org.catrobat.catroid.formulaeditor.Formula;
import org.catrobat.catroid.formulaeditor.InterpretationException;
import org.teavm.jso.JSBody;

/**
 * What the app hands to Android: the open URL brick's link opens in a new tab, and the save and share plot bricks'
 * SVG becomes a download. Browsers allow both only right after a click or key press; without one, a small prompt over
 * the stage asks first, and its Open or Save is that click. The player page itself never navigates.
 */
public final class BrowserHandoff {
	private BrowserHandoff() {
	}

	/** The open URL brick without its web request and trust dialog (OpenUrlAction): the browser opens the link. */
	public static final class OpenUrl extends Action {
		private final Scope scope;
		private final Formula formula;

		public OpenUrl(Scope scope, Formula formula) {
			this.scope = scope;
			this.formula = formula;
		}

		@Override
		public boolean act(float delta) {
			String url;
			try {
				url = null == formula ? null : formula.interpretString(scope);
			} catch (InterpretationException e) {
				return true; // as WebAction.interpretUrl
			}
			if (null != url) {
				open(url);
			}
			return true;
		}
	}

	/** WebAction.interpretUrl: https:// unless the text says http(s), and only its first line. */
	static void open(String text) {
		String url = text.startsWith("http://") || text.startsWith("https://") ? text : "https://" + text;
		int newline = url.indexOf('\n');
		hand(newline < 0 ? url : url.substring(0, newline), null, null);
	}

	/** Offers an SVG the project made (save or share plot) as a download. */
	public static void download(String fileName, String svg) {
		hand(null, fileName, svg);
	}

	/**
	 * Opens url (http or https only) in a new tab, or downloads svg as fileName. Without a recent click (the browser
	 * would block it) a prompt asks; one per link or file name at a time, so a loop cannot pile them up.
	 */
	@JSBody(params = {"url", "fileName", "svg"}, script = "var href, target;"
			+ "if (null !== fileName) {"
			+ "  target = fileName; href = URL.createObjectURL(new Blob([svg], {type: 'image/svg+xml'}));"
			+ "} else {"
			+ "  try { var parsed = new URL(url); } catch (e) { return; }"
			+ "  if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') return;"
			+ "  target = href = parsed.href;"
			+ "}"
			+ "var link = document.createElement('a'); link.href = href; link.rel = 'noopener noreferrer';"
			+ "if (null !== fileName) link.download = fileName; else link.target = '_blank';"
			+ "var done = function () { if (null !== fileName) setTimeout(function () { URL.revokeObjectURL(href); }, 60000); };"
			+ "if (navigator.userActivation && navigator.userActivation.isActive) { link.click(); done(); return; }"
			+ "var list = document.getElementById('player-handoff');"
			+ "if (!list) {"
			+ "  list = document.createElement('div'); list.id = 'player-handoff';"
			+ "  list.style.cssText = 'position:fixed;left:16px;right:16px;bottom:16px;display:flex;flex-direction:column;"
			+ "align-items:center;gap:8px;pointer-events:none;z-index:10';"
			+ "  document.body.append(list);"
			+ "}"
			+ "for (var i = 0; i < list.children.length; i++) {"
			+ "  if (list.children[i].dataset.target === target) { done(); return; }"
			+ "}"
			+ "var box = document.createElement('div'); box.dataset.target = target;"
			+ "box.setAttribute('role', 'dialog'); box.setAttribute('aria-label', null !== fileName ? 'Save file' : 'Open link');"
			+ "box.style.cssText = 'pointer-events:auto;box-sizing:border-box;width:100%;max-width:420px;padding:16px;"
			+ "border-radius:12px;background:#fff;color:#1a1a1a;box-shadow:0 4px 16px rgba(0,0,0,.4);font:16px/1.4 system-ui,sans-serif';"
			+ "var text = document.createElement('p'); text.style.cssText = 'margin:0 0 12px;overflow-wrap:anywhere';"
			+ "var name = document.createElement('strong'); name.textContent = target;"
			+ "text.append(null !== fileName ? 'This project wants to save ' : 'This project wants to open ', name);"
			+ "var buttons = document.createElement('div'); buttons.style.cssText = 'display:flex;justify-content:flex-end;gap:8px';"
			+ "var look = 'min-height:44px;padding:0 16px;border-radius:8px;font:600 16px system-ui,sans-serif;cursor:pointer;"
			+ "display:inline-flex;align-items:center;text-decoration:none;';"
			+ "link.textContent = null !== fileName ? 'Save' : 'Open';"
			+ "link.style.cssText = look + 'border:0;background:#007f8f;color:#fff';"
			+ "var cancel = document.createElement('button'); cancel.type = 'button'; cancel.textContent = 'Cancel';"
			+ "cancel.style.cssText = look + 'border:1px solid #767676;background:#fff;color:#1a1a1a';"
			+ "var close = function () { box.remove(); done(); };"
			// After the click has followed the link: a link removed while clicked might not.
			+ "link.addEventListener('click', function () { setTimeout(close, 0); }); cancel.addEventListener('click', close);"
			+ "box.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });"
			+ "buttons.append(cancel, link); box.append(text, buttons); list.append(box); link.focus();")
	private static native void hand(String url, String fileName, String svg);
}
