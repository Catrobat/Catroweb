package org.catrobat.web.codeview;

import android.view.View;

import org.catrobat.catroid.ProjectManager;
import org.catrobat.catroid.common.LookData;
import org.catrobat.catroid.common.Nameable;
import org.catrobat.catroid.common.SoundInfo;
import org.catrobat.catroid.content.GroupItemSprite;
import org.catrobat.catroid.content.GroupSprite;
import org.catrobat.catroid.content.Project;
import org.catrobat.catroid.content.Scene;
import org.catrobat.catroid.content.Script;
import org.catrobat.catroid.content.Sprite;
import org.catrobat.catroid.content.bricks.Brick;
import org.catrobat.catroid.content.bricks.CompositeBrick;
import org.catrobat.web.android.WebContext;
import org.catrobat.web.loader.ProjectLoader;
import org.teavm.jso.JSBody;
import org.teavm.jso.JSFunctor;
import org.teavm.jso.JSObject;

import java.util.List;

/**
 * The code view's export: reads a project's code.xml with the player's loader (so every project the player plays,
 * from Catrobat language 0.9 on) and writes each brick as the app shows it, from the brick's own getView over
 * CodeViews. Its strings are markers (scripts/export_code_resources.py) that share puts in the viewer's language.
 * Built as its own script (code-export.js) for Node; web/code-export/export.mjs runs it.
 *
 * <pre>
 * {"scenes": [{"name", "objects": [object]}], "files_in_scene_folders": bool}
 * object: {"name", "looks": [{"name", "file"}], "sounds": [{"name", "file"}], "scripts": [brick], "group": [object]?}
 * brick:  {"type", "category", "commented_out", "parts": [{"kind": label|formula|choice, "text"}],
 *          "bricks": [brick]? (a script's), "sections": [{"parts", "bricks"}]? (an if's else and end, a loop's end),
 *          "error"? (getView failed)}
 * </pre>
 */
public final class CodeExport {
	private static int missingLayouts;

	private CodeExport() {
	}

	@JSFunctor
	interface Exporter extends JSObject {
		String export(String xml);
	}

	public static void main(String[] args) {
		// XStream sniffs the JVM from these; TeaVM leaves them unset (as WebLauncher).
		for (String key : new String[] {"java.vm.vendor", "java.vm.name", "java.vendor", "java.specification.vendor",
				"java.specification.name"}) {
			System.setProperty(key, "TeaVM");
		}
		System.setProperty("java.specification.version", "21");
		try {
			new ProjectManager(null);
		} catch (RuntimeException e) {
			// It registers itself, then reads the device's downloaded-project list, which the web has not.
		}
		register(CodeExport::export);
	}

	@JSBody(params = "exporter", script = "globalThis.catrobatCodeExport = exporter;")
	private static native void register(Exporter exporter);

	static void missingLayout(int id) {
		missingLayouts++;
	}

	/** The project's code as JSON, or {"error": why} if it cannot be read. */
	public static String export(String xml) {
		try {
			Project project = ProjectLoader.parse(xml);
			missingLayouts = 0;
			Json json = new Json();
			json.open('{').key("files_in_scene_folders").value(!ProjectLoader.filesWithoutSceneFolder()).key("scenes").open('[');
			ProjectManager manager = ProjectManager.getInstance();
			manager.setCurrentProject(project);
			for (Scene scene : project.getSceneList()) {
				manager.setCurrentlyEditedScene(scene);
				project.getBroadcastMessageContainer().update(); // the scene's messages, as the app's ScriptFragment does
				json.open('{').key("name").value(scene.getName()).key("objects").open('[');
				List<Sprite> sprites = scene.getSpriteList();
				for (int i = 0; i < sprites.size(); i++) {
					Sprite sprite = sprites.get(i);
					json.open('{');
					object(json, scene, sprite);
					if (sprite instanceof GroupSprite) {
						json.key("group").open('[');
						while (i + 1 < sprites.size() && sprites.get(i + 1) instanceof GroupItemSprite) {
							json.open('{');
							object(json, scene, sprites.get(++i));
							json.close('}');
						}
						json.close(']');
					}
					json.close('}');
				}
				json.close(']').close('}');
			}
			json.close(']').key("missing_layouts").value(missingLayouts).close('}');
			return json.toString();
		} catch (Throwable e) {
			return new Json().open('{').key("error").value(String.valueOf(e.getMessage() != null ? e.getMessage() : e)).close('}').toString();
		}
	}

	private static void object(Json json, Scene scene, Sprite sprite) {
		ProjectManager manager = ProjectManager.getInstance();
		manager.setCurrentlyEditedScene(scene);
		manager.setCurrentSprite(sprite);
		manager.setCurrentlyEditedScene(scene); // setCurrentSprite may pick the sprite's first scene
		json.key("name").value(sprite.getName()).key("looks").open('[');
		for (LookData look : sprite.getLookList()) {
			json.open('{').key("name").value(look.getName()).key("file").value(look.getXstreamFileName()).close('}');
		}
		json.close(']').key("sounds").open('[');
		for (SoundInfo sound : sprite.getSoundList()) {
			json.open('{').key("name").value(sound.getName()).key("file").value(sound.getXstreamFileName()).close('}');
		}
		json.close(']').key("scripts").open('[');
		for (Script script : sprite.getScriptList()) {
			json.open('{');
			brick(json, script.getClass().getSimpleName(), script.getScriptBrick(), script.isCommentedOut());
			bricks(json, "bricks", script.getBrickList());
			json.close('}');
		}
		json.close(']');
	}

	private static void bricks(Json json, String key, List<Brick> bricks) {
		json.key(key).open('[');
		for (Brick brick : bricks) {
			json.open('{');
			brick(json, brick.getClass().getSimpleName(), brick, brick.isCommentedOut());
			if (brick instanceof CompositeBrick composite) {
				// The app shows an if's "else" and "end if", a loop's end, as bricks of their own (getAllParts).
				List<Brick> parts = brick.getAllParts();
				json.key("sections").open('[').open('{');
				bricks(json, "bricks", composite.getNestedBricks());
				json.close('}');
				for (int i = 1; i < parts.size(); i++) {
					json.open('{');
					parts(json, parts.get(i));
					bricks(json, "bricks", i == 1 && composite.hasSecondaryList() ? composite.getSecondaryNestedBricks() : List.of());
					json.close('}');
				}
				json.close(']');
			}
			json.close('}');
		}
		json.close(']');
	}

	private static void brick(Json json, String type, Brick brick, boolean commentedOut) {
		json.key("type").value(type).key("commented_out").value(commentedOut);
		parts(json, brick);
	}

	/** The brick's category and text, as its getView fills its layout; "error" if that fails. */
	private static void parts(Json json, Brick brick) {
		View view;
		try {
			view = brick.getView(WebContext.INSTANCE);
		} catch (Throwable e) {
			json.key("category").value("unknown").key("parts").open('[').close(']')
					.key("error").value(e.getClass().getSimpleName() + ": " + e.getMessage());
			return;
		}
		String[] category = new String[1];
		json.key("parts").open('[');
		render(json, view, category);
		json.close(']').key("category").value(null == category[0] ? "unknown" : category[0]);
	}

	private static void render(Json json, View view, String[] category) {
		if (null == view || View.GONE == view.getVisibility()) {
			return;
		}
		if (view instanceof CodeViews.Parent group) {
			if (null == category[0] && group.style().startsWith("BrickContainer.")) {
				category[0] = group.style().split("\\.")[1].toLowerCase();
			}
			for (View child : group.children()) {
				render(json, child, category);
			}
		} else if (view instanceof CodeViews.Leaf leaf && leaf.style().startsWith("Category.") && null == category[0]) {
			category[0] = leaf.style().substring("Category.".length());
		} else if (view instanceof CodeViews.Text text) {
			String value = trim(text.getText().toString());
			boolean formula = text.style().startsWith("BrickEditText");
			if (formula || !value.isEmpty()) {
				json.open('{').key("kind").value(formula ? "formula" : "label").key("text").value(value).close('}');
			}
		} else if (view instanceof CodeViews.Spinner spinner) {
			Object item = spinner.getSelectedItem();
			String value = item instanceof Nameable nameable ? nameable.getName() : null == item ? "" : String.valueOf(item);
			json.open('{').key("kind").value("choice").key("text").value(null == value ? "" : value);
			if (null == item) {
				json.key("missing").value(true); // nothing selected: what scripts/check_code_export.py looks for
			}
			json.close('}');
		}
	}

	/** Without spaces around it; String.trim would also take the markers' control characters at its ends. */
	private static String trim(String text) {
		int start = 0;
		int end = text.length();
		while (start < end && isSpace(text.charAt(start))) {
			start++;
		}
		while (end > start && isSpace(text.charAt(end - 1))) {
			end--;
		}
		return text.substring(start, end);
	}

	private static boolean isSpace(char c) {
		return '\n' == c || '\r' == c || '\t' == c || Character.isSpaceChar(c);
	}

	/** Just enough JSON: commas come from what was written before. */
	static final class Json {
		private final StringBuilder out = new StringBuilder();

		Json open(char bracket) {
			comma();
			out.append(bracket);
			return this;
		}

		Json close(char bracket) {
			out.append(bracket);
			return this;
		}

		Json key(String key) {
			comma();
			string(key);
			out.append(':');
			return this;
		}

		Json value(String value) {
			if (null == value) {
				out.append("null");
			} else {
				string(value);
			}
			return this;
		}

		Json value(boolean value) {
			out.append(value);
			return this;
		}

		Json value(int value) {
			out.append(value);
			return this;
		}

		private void comma() {
			if (out.length() > 0) {
				char last = out.charAt(out.length() - 1);
				if ('{' != last && '[' != last && ':' != last) {
					out.append(',');
				}
			}
		}

		private void string(String s) {
			out.append('"');
			for (int i = 0; i < s.length(); i++) {
				char c = s.charAt(i);
				switch (c) {
					case '"' -> out.append("\\\"");
					case '\\' -> out.append("\\\\");
					case '\n' -> out.append("\\n");
					case '\r' -> out.append("\\r");
					case '\t' -> out.append("\\t");
					default -> {
						if (c < 0x20 || c == 0x2028 || c == 0x2029) {
							out.append(String.format("\\u%04x", (int) c));
						} else {
							out.append(c);
						}
					}
				}
			}
			out.append('"');
		}

		@Override
		public String toString() {
			return out.toString();
		}
	}
}
