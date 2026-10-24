package org.catrobat.web.loader;

import org.teavm.jso.browser.Window;
import org.teavm.jso.dom.html.HTMLElement;

/**
 * index.html#check: loads every project in /project-files/check.txt (written by scripts/check_loader.py: per project
 * its id, a newline and its code.xml, separated by NUL characters) and writes one line per project into
 * &lt;pre id="loader-check"&gt;, for check_loader.py to compare with the XML. No stage is started. One request for all
 * projects: headless Chrome sometimes never sends the next of many synchronous requests.
 */
public final class LoaderCheck {
	private LoaderCheck() {
	}

	public static void run() {
		StringBuilder lines = new StringBuilder();
		for (String project : ProjectLoader.fetch("/project-files/check.txt").split("\u0000")) {
			int newline = project.indexOf('\n');
			String id = project.substring(0, newline);
			String result;
			try {
				result = ProjectLoader.summary(ProjectLoader.parse(project.substring(newline + 1)));
			} catch (Throwable e) { // one broken project must not hide the others
				result = ("error: " + e).replaceAll("\\s*\\n\\s*", " | "); // one line per project
			}
			lines.append(id).append('\t').append(result).append('\n');
		}
		HTMLElement output = Window.current().getDocument().createElement("pre");
		output.setAttribute("id", "loader-check");
		output.setTextContent(lines.toString());
		Window.current().getDocument().getBody().appendChild(output);
	}
}
