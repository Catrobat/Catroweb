package org.catrobat.web.loader;

import com.badlogic.gdx.Gdx;
import com.thoughtworks.xstream.converters.ConversionException;

import org.catrobat.catroid.BuildConfig;
import org.catrobat.catroid.ProjectManager;
import org.catrobat.catroid.R;
import org.catrobat.catroid.common.Constants;
import org.catrobat.catroid.common.LookData;
import org.catrobat.catroid.common.ScreenModes;
import org.catrobat.catroid.common.SoundInfo;
import org.catrobat.catroid.content.Project;
import org.catrobat.catroid.content.Scene;
import org.catrobat.catroid.content.Script;
import org.catrobat.catroid.content.Sprite;
import org.catrobat.catroid.content.WhenBounceOffScript;
import org.catrobat.catroid.content.backwardcompatibility.LegacyProjectWithoutScenes;
import org.catrobat.catroid.content.backwardcompatibility.ProjectUntilLanguageVersion0999;
import org.catrobat.catroid.content.backwardcompatibility.SceneUntilLanguageVersion0999;
import org.catrobat.catroid.content.bricks.Brick;
import org.catrobat.catroid.io.XstreamSerializer;
import org.catrobat.web.compat.WebCompat;
import org.catrobat.web.java.Dom;
import org.teavm.interop.Async;
import org.teavm.interop.AsyncCallback;
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
import java.util.LinkedHashSet;
import java.util.Set;

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

	/** The project as the app plays it: read, then updated to the current language version. */
	static Project parse(String xml) {
		Project project = read(xml);
		updateToCurrentVersion(project);
		return project;
	}

	/**
	 * Projects without scenes (before Catrobat language 0.992) keep their looks and sounds in the project's images/
	 * and sounds/, and share serves them there: only the app moves them into the scene's folder.
	 * ponytail: one project per page, so the last one read decides; per project if a page ever plays several.
	 */
	private static boolean filesWithoutSceneFolder;

	/** The project exactly as code.xml describes it, which LoaderCheck compares with the XML. */
	static Project read(String xml) {
		Element root = DOMParser.create().parseFromString(xml, "text/xml").getDocumentElement();
		if (0 != root.getElementsByTagName("parsererror").getLength()) { // the browser's way of saying it is not XML
			throw new IllegalArgumentException("code.xml is not valid XML");
		}
		double version = languageVersion(root);
		if (version > Constants.CURRENT_CATROBAT_LANGUAGE_VERSION) { // as the app: made by a newer one
			throw new IllegalArgumentException("This project needs a newer player (Catrobat language version " + version + ")");
		}
		if (version < 0.9 && version != 0.8) { // as ProjectManager.loadProject
			throw new IllegalArgumentException("Projects before Catrobat language version 0.9 cannot be played");
		}
		// What XstreamSerializer.loadProject does: older projects are read into legacy classes and converted.
		XstreamSerializer serializer = XstreamSerializer.getInstance();
		boolean scenesEnabled = "true".equals(headerField(root, "scenesEnabled")); // as ProjectMetaDataParser
		filesWithoutSceneFolder = !scenesEnabled;
		Project project;
		if (!scenesEnabled) {
			project = withoutScenes((LegacyProjectWithoutScenes) unmarshalAs(serializer, root, LegacyProjectWithoutScenes.class,
					Scene.class));
		} else if (version < 0.9991) {
			project = ((ProjectUntilLanguageVersion0999) unmarshalAs(serializer, root, ProjectUntilLanguageVersion0999.class,
					SceneUntilLanguageVersion0999.class)).toProject();
		} else {
			project = (Project) unmarshal(serializer, root);
			for (Scene scene : project.getSceneList()) {
				scene.setProject(project);
			}
		}
		project.checkForInvisibleSprites();
		return project;
	}

	/** Read with XStream set up for those legacy classes, then back for the current ones. */
	private static Object unmarshalAs(XstreamSerializer serializer, Element root, Class<?> projectClass, Class<?> sceneClass) {
		serializer.prepareXstream(projectClass, sceneClass);
		try {
			return unmarshal(serializer, root);
		} finally {
			serializer.prepareXstream(Project.class, Scene.class);
		}
	}

	/**
	 * BackwardCompatibleCatrobatLanguageXStream.getProjectFromXML: XML XStream cannot read (from before Catrobat
	 * language 0.93) is upgraded by modifyXMLToSupportUnknownFields and read again.
	 */
	private static Object unmarshal(XstreamSerializer serializer, Element root) {
		try {
			return serializer.getXstream().unmarshal(new BrowserDomReader(root));
		} catch (ConversionException e) {
			Dom.edit(root.getOwnerDocument());
			serializer.getXstream().modifyXMLToSupportUnknownFields(null); // edits that DOM instead of code.xml's file
			return serializer.getXstream().unmarshal(new BrowserDomReader(root));
		}
	}

	/**
	 * LegacyProjectWithoutScenes.toProject without its file moves (images/ and sounds/ into the scene's folder), which
	 * on the web is filesWithoutSceneFolder.
	 */
	private static Project withoutScenes(LegacyProjectWithoutScenes legacy) {
		Project project = new Project();
		project.setXmlHeader(legacy.getXmlHeader());
		project.getSettings().addAll(legacy.getSettings());
		project.getUserVariables().addAll(legacy.getProjectUserVariables());
		project.getUserLists().addAll(legacy.getProjectUserLists());
		for (Sprite sprite : legacy.getSpriteList()) {
			sprite.getUserVariables().addAll(legacy.getSpriteUserVariables(sprite));
			sprite.getUserLists().addAll(legacy.getSpriteUserLists(sprite));
		}
		Scene scene = new Scene(WebCompat.appContext().getString(R.string.default_scene_name), project);
		scene.getSpriteList().addAll(legacy.getSpriteList());
		project.addScene(scene);
		return project;
	}

	/**
	 * What ProjectManager.loadProject does to a project after reading it: the updates to the current language
	 * version, then the scripts' parents and bounce-off targets. Left out: the permissions file, the background's
	 * localised name and the device settings, which concern the app, and makeShallowCopiesDeepAgain (until 0.999),
	 * which copies files several looks or sounds share so that editing one leaves the others alone; the stage never
	 * writes them.
	 */
	private static void updateToCurrentVersion(Project project) {
		double version = project.getCatrobatLanguageVersion();
		// As in loadProject, the project is the current one during the updates: updateCollisionScriptsTo996 looks
		// up the sprites in its scene.
		ProjectManager.getInstance().setCurrentProject(project);
		if (version <= 0.91) {
			project.setScreenMode(ScreenModes.STRETCH);
		}
		if (version <= 0.992) {
			ProjectManager.updateCollisionFormulasTo993(project);
		}
		if (version <= 0.993) {
			ProjectManager.updateSetPenColorFormulasTo994(project);
		}
		if (version <= 0.994) {
			ProjectManager.updateArduinoValuesTo995(project);
		}
		if (version <= 0.995) {
			ProjectManager.updateCollisionScriptsTo996(project);
		}
		if (version <= 0.9993) {
			ProjectManager.updateScriptsToTreeStructure(project);
		}
		if (version <= 0.9999995) {
			ProjectManager.updateBackgroundIndexTo9999995(project);
		}
		if (version < 999.9 && !BuildConfig.FEATURE_LIST_AS_BASIC_DATATYPE) {
			ProjectManager.flattenAllLists(project);
		}
		if (version <= 1.03) {
			ProjectManager.updateDirectionProperty(project);
		}
		project.setCatrobatLanguageVersion(Constants.CURRENT_CATROBAT_LANGUAGE_VERSION);
		for (Scene scene : project.getSceneList()) {
			for (Sprite sprite : scene.getSpriteList()) {
				for (Script script : sprite.getScriptList()) {
					script.setParents();
					if (script instanceof WhenBounceOffScript bounceOff) {
						bounceOff.updateSpriteToCollideWith(scene);
					}
				}
			}
		}
	}

	private static double languageVersion(Element root) {
		String version = headerField(root, "catrobatLanguageVersion");
		try {
			return null == version ? 0 : Double.parseDouble(version);
		} catch (NumberFormatException e) {
			throw new IllegalArgumentException("Unknown Catrobat language version: " + version, e);
		}
	}

	private static String headerField(Element root, String name) {
		NodeList<? extends Element> field = root.getElementsByTagName(name);
		return 0 == field.getLength() ? null : field.item(0).getTextContent().trim();
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
					if (null != look.getFile()) {
						continue; // the same look in several sprites (an XStream reference), already loaded
					}
					File file = lookFile(scene, look);
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
					if (null != sound.getFile()) {
						continue;
					}
					File file = soundFile(scene, sound);
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

	private static File lookFile(Scene scene, LookData look) {
		return new File(new File(scene.getDirectory(), "images"), look.getXstreamFileName());
	}

	private static File soundFile(Scene scene, SoundInfo sound) {
		return new File(new File(scene.getDirectory(), "sounds"), sound.getXstreamFileName());
	}

	/**
	 * Starts downloading every look and sound, eight at a time, while the start screen waits for its click;
	 * fetchBytes then takes them from memory instead of one synchronous request after the other.
	 */
	public static void prefetch(Project project, String projectId) {
		Set<String> urls = new LinkedHashSet<>();
		for (Scene scene : project.getSceneList()) {
			for (Sprite sprite : scene.getSpriteList()) {
				for (LookData look : sprite.getLookList()) {
					urls.add(fileUrl(projectId, lookFile(scene, look)));
				}
				for (SoundInfo sound : sprite.getSoundList()) {
					urls.add(fileUrl(projectId, soundFile(scene, sound)));
				}
			}
		}
		startPrefetch(urls.toArray(new String[0]));
	}

	/** A file that failed or timed out stays out of the cache: fetchBytes asks again and reports the error. */
	@JSBody(params = "urls", script = "var files = {}, next = 0;"
			+ "function one() {"
			+ "  if (next >= urls.length) return Promise.resolve();"
			+ "  var url = urls[next++];"
			+ "  var options = AbortSignal.timeout ? {signal: AbortSignal.timeout(30000)} : {};" // a stalled file must not block the start
			+ "  return fetch(url, options).then(function (response) {"
			+ "    if (response.status === 404) files[url] = new Int8Array(0);"
			+ "    else if (response.status === 200) return response.arrayBuffer().then(function (b) { files[url] = new Int8Array(b); });"
			+ "  }).catch(function () {}).then(one);"
			+ "}"
			+ "var workers = [];"
			+ "for (var i = 0; i < 8; i++) workers.push(one());"
			+ "window.catrobatPrefetch = {files: files, done: Promise.all(workers)};")
	private static native void startPrefetch(String[] urls);

	/** Blocks the calling TeaVM thread until prefetch has every file (or gave up on it). */
	@Async
	public static native Void awaitPrefetch();

	private static void awaitPrefetch(AsyncCallback<Void> done) {
		whenPrefetched(() -> done.complete(null));
	}

	@JSBody(params = "ready", script = "var p = window.catrobatPrefetch;"
			+ "(p ? p.done : Promise.resolve()).then(function () { ready(); });")
	private static native void whenPrefetched(Sounds.Callback ready);

	/**
	 * The file under the project directory, from share: /project-files/{id}/assets/{scene}/{images|sounds}/{name},
	 * without {scene}/ for a project without scenes.
	 */
	private static String fileUrl(String projectId, File file) {
		File folder = file.getParentFile();
		String scene = filesWithoutSceneFolder ? "" : encode(folder.getParentFile().getName()) + "/";
		return assetUrl(projectId, scene + folder.getName() + "/" + encode(file.getName()));
	}

	private static byte[] fetchBytes(String projectId, File file) {
		return fetchBytes(fileUrl(projectId, file));
	}

	@JSBody(params = "segment", script = "return encodeURIComponent(segment);")
	private static native String encode(String segment);

	/**
	 * Empty for a file share does not have. From prefetch when it has the file; otherwise a synchronous request,
	 * which cannot ask for an ArrayBuffer: the x-user-defined charset keeps each byte in one char.
	 */
	@JSBody(params = "url", script = "var p = window.catrobatPrefetch, cached = p && p.files[url];"
			+ "if (cached) { delete p.files[url]; return cached; }"
			+ "var request = new XMLHttpRequest();"
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
