package org.catrobat.web.loader;

import org.teavm.jso.browser.Window;
import org.teavm.jso.dom.html.HTMLElement;

/**
 * index.html#check: loads every project in /project-files/index.txt and writes one line per project into
 * &lt;pre id="loader-check"&gt;, for scripts/check_loader.py to compare with the XML. No stage is started.
 */
public final class LoaderCheck {
	private LoaderCheck() {
	}

	public static void run() {
		StringBuilder lines = new StringBuilder();
		for (String id : ProjectLoader.fetch("/project-files/index.txt").split("\n")) {
			if (id.isBlank()) {
				continue;
			}
			String result;
			try {
				result = ProjectLoader.summary(ProjectLoader.load(id));
			} catch (Throwable e) { // one broken project must not hide the others
				result = ("error: " + e).replaceAll("\\s*\\n\\s*", " | "); // one line per project
			}
			lines.append(id).append('\t').append(result).append('\n');
		}
		HTMLElement output = Window.current().getDocument().createElement("pre");
		output.setAttribute("id", "loader-check");
		output.setTextContent(lines.toString());
		Window.current().getDocument().getBody().appendChild(output);
		try {
			ProjectLoader.fetch("/project-files/done"); // tells scripts/smoke_server.py that the page is done
		} catch (IllegalStateException e) {
			// not served by the smoke server
		}
	}

}
