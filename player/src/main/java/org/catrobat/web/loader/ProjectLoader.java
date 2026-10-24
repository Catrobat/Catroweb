package org.catrobat.web.loader;

import com.badlogic.gdx.Gdx;

import org.catrobat.catroid.common.LookData;
import org.catrobat.catroid.common.SoundInfo;
import org.catrobat.catroid.content.Project;
import org.catrobat.catroid.content.Scene;
import org.catrobat.catroid.content.Script;
import org.catrobat.catroid.content.Sprite;
import org.catrobat.catroid.content.bricks.Brick;
import org.catrobat.catroid.io.XstreamSerializer;
import org.teavm.jso.JSBody;
import org.teavm.jso.JSByRef;
import org.teavm.jso.ajax.XMLHttpRequest;
import org.teavm.jso.browser.Location;
import org.teavm.jso.browser.Window;
import org.teavm.jso.dom.xml.DOMParser;
import org.teavm.jso.dom.xml.Element;
import org.teavm.jso.dom.xml.NodeList;

import java.io.File;
import java.util.Iterator;

/**
 * Loads a share project with Catroid's own XStream setup (its aliases, converters and field order), so the web
 * player reads code.xml exactly like the app. Only the XML parsing is the browser's.
 */
public final class ProjectLoader {
	private ProjectLoader() {
	}

	/**
	 * Share serves the extracted files of visible projects under /project-files/{id}/assets/. The player runs on its
	 * own host, play.<domain>, so project code never shares an origin with share's login cookies; it reads the files
	 * from share.<domain>, which allows that origin (PlayerCorsListener). Anywhere else (tests, development) the same
	 * host serves both.
	 */
	private static String assetUrl(String projectId, String path) {
		Location location = Window.current().getLocation();
		String host = location.getHost(); // with the port, if any
		String share = host.startsWith("play.") ? location.getProtocol() + "//share." + host.substring("play.".length()) : "";
		return share + "/project-files/" + projectId + "/assets/" + path;
	}

	public static Project load(String projectId) {
		if (!projectId.matches("[A-Za-z0-9-]+")) { // it comes from the URL and goes into one
			throw new IllegalArgumentException("Not a project id: " + projectId);
		}
		Project project = parse(fetch(assetUrl(projectId, "code.xml")));
		project.setDirectory(new File("/projects/" + projectId)); // where the scenes' looks and sounds go
		return project;
	}

	static Project parse(String xml) {
		Element root = DOMParser.create().parseFromString(xml, "text/xml").getDocumentElement();
		if (0 != root.getElementsByTagName("parsererror").getLength()) { // the browser's way of saying it is not XML
			throw new IllegalArgumentException("code.xml is not valid XML");
		}
		// XstreamSerializer.loadProject reads older projects into legacy classes and converts them; not yet here.
		if (0 == root.getElementsByTagName("scenes").getLength() || languageVersion(root) < 0.9991) {
			throw new IllegalArgumentException("Projects before Catrobat language version 0.9991 cannot be played here yet");
		}
		// ponytail: the app retries a ConversionException after modifyXMLToSupportUnknownFields (java.io.File + DOM);
		// not here yet, so such a project fails loudly instead.
		Project project = (Project) XstreamSerializer.getInstance().getXstream().unmarshal(new BrowserDomReader(root));
		for (Scene scene : project.getSceneList()) { // the rest of what loadProject does after reading the XML
			scene.setProject(project);
		}
		project.checkForInvisibleSprites();
		return project;
	}

	private static double languageVersion(Element root) {
		NodeList<? extends Element> version = root.getElementsByTagName("catrobatLanguageVersion");
		return 0 == version.getLength() ? 0 : Double.parseDouble(version.item(0).getTextContent().trim());
	}

	/**
	 * "1 scenes, 9 sprites, 25 scripts, 47 bricks" and then each sprite, script and top-level brick class in order:
	 * what scripts/check_loader.py reads from the XML. The classes catch a brick that loaded as the wrong one.
	 */
	public static String summary(Project project) {
		int sprites = 0;
		int scripts = 0;
		int bricks = 0;
		StringBuilder classes = new StringBuilder();
		for (Scene scene : project.getSceneList()) {
			for (Sprite sprite : scene.getSpriteList()) {
				sprites++;
				classes.append(' ').append(simpleName(sprite)).append(':');
				for (Script script : sprite.getScriptList()) {
					scripts++;
					classes.append(' ').append(simpleName(script)).append('(');
					for (Brick brick : script.getBrickList()) {
						bricks++;
						classes.append(' ').append(simpleName(brick));
					}
					classes.append(" )");
				}
			}
		}
		return project.getSceneList().size() + " scenes, " + sprites + " sprites, " + scripts + " scripts, " + bricks
				+ " bricks |" + classes;
	}

	private static String simpleName(Object object) {
		String name = object.getClass().getName();
		return name.substring(name.lastIndexOf('.') + 1);
	}

	/**
	 * What XstreamSerializer.setFileReferences does on Android once the project is on disk, for the looks: each gets
	 * its file, those missing on share (404) are dropped; any other failure stops the load. They go into the browser's
	 * memory (Gdx.files.local, which Files.absolute is redirected to), so this needs the stage's Gdx.files.
	 */
	public static void loadLooks(Project project, String projectId) {
		for (Scene scene : project.getSceneList()) {
			for (Sprite sprite : scene.getSpriteList()) {
				for (Iterator<LookData> looks = sprite.getLookList().iterator(); looks.hasNext(); ) {
					LookData look = looks.next();
					File file = new File(new File(scene.getDirectory(), "images"), look.getXstreamFileName());
					byte[] bytes = fetchBytes(projectId, file);
					if (0 == bytes.length) {
						looks.remove();
						continue;
					}
					Gdx.files.local(file.getAbsolutePath()).writeBytes(bytes, false);
					look.setFile(file);
				}
			}
		}
	}

	/**
	 * The same for the sounds, which Sounds decodes; before the stage exists, because Catroid reads their durations
	 * while it builds the scripts' actions (Sounds.awaitDecoded).
	 */
	public static void loadSounds(Project project, String projectId) {
		for (Scene scene : project.getSceneList()) {
			for (Sprite sprite : scene.getSpriteList()) {
				for (Iterator<SoundInfo> sounds = sprite.getSoundList().iterator(); sounds.hasNext(); ) {
					SoundInfo sound = sounds.next();
					File file = new File(new File(scene.getDirectory(), "sounds"), sound.getXstreamFileName());
					byte[] bytes = fetchBytes(projectId, file);
					if (0 == bytes.length) {
						sounds.remove();
						continue;
					}
					Sounds.decode(file.getAbsolutePath(), bytes);
					sound.setFile(file);
				}
			}
		}
	}

	/** The file under the project directory, from share: /project-files/{id}/assets/{scene}/{images|sounds}/{name}. */
	private static byte[] fetchBytes(String projectId, File file) {
		File folder = file.getParentFile();
		return fetchBytes(assetUrl(projectId, encode(folder.getParentFile().getName()) + "/" + folder.getName() + "/"
				+ encode(file.getName())));
	}

	@JSBody(params = "segment", script = "return encodeURIComponent(segment);")
	private static native String encode(String segment);

	/**
	 * Empty for a file share does not have. A synchronous request cannot ask for an ArrayBuffer; the x-user-defined
	 * charset keeps each byte in one char.
	 */
	@JSBody(params = "url", script = "var request = new XMLHttpRequest();"
			+ "request.open('GET', url, false);"
			+ "request.overrideMimeType('text/plain; charset=x-user-defined');"
			+ "request.send();"
			+ "if (request.status === 404) return new Int8Array(0);"
			+ "if (request.status !== 200) throw new Error('Could not load ' + url + ': HTTP ' + request.status);"
			+ "var text = request.responseText, bytes = new Int8Array(text.length);"
			+ "for (var i = 0; i < text.length; i++) bytes[i] = text.charCodeAt(i) & 255;"
			+ "return bytes;")
	@JSByRef
	private static native byte[] fetchBytes(String url);

	static String fetch(String url) {
		XMLHttpRequest request = XMLHttpRequest.create();
		request.open("GET", url, false); // ponytail: synchronous on purpose until the player has a loading screen
		request.send();
		if (200 != request.getStatus()) {
			throw new IllegalStateException("Could not load " + url + ": HTTP " + request.getStatus());
		}
		return request.getResponseText();
	}
}
