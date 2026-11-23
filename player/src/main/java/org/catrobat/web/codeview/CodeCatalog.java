package org.catrobat.web.codeview;

import android.content.ContextWrapper;
import android.content.SharedPreferences;

import org.catrobat.catroid.ProjectManager;
import org.catrobat.catroid.content.Project;
import org.catrobat.catroid.content.Scene;
import org.catrobat.catroid.content.Sprite;
import org.catrobat.catroid.content.bricks.Brick;
import org.catrobat.catroid.formulaeditor.Functions;
import org.catrobat.catroid.formulaeditor.InternToExternGenerator;
import org.catrobat.catroid.formulaeditor.Operators;
import org.catrobat.catroid.formulaeditor.Sensors;
import org.catrobat.catroid.ui.fragment.CategoryBricksFactory;
import org.catrobat.web.android.WebContext;
import org.teavm.jso.JSBody;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashSet;
import java.util.List;
import java.util.Map;
import java.util.Set;

/**
 * The catalog of Catroid's bricks and formula elements, as the app's brick picker and formula editor list them: every
 * category CategoryBricksFactory knows (each category_* string but the recently used ones) with every brick set
 * enabled, each brick written like the code export's (CodeExport.bricks), and the Functions, Sensors and Operators
 * with the marker of their name in the formula editor (InternToExternGenerator). Nothing in it is listed by hand.
 *
 * <pre>
 * {"categories": [{"key": "category_motion", "name": marker, "bricks": [brick], "error"?}],
 *  "formula": {"functions"|"sensors"|"operators": [{"name": "SIN", "label": marker | null}]}}
 * </pre>
 * A brick's "type" is its code.xml type: the script's class for a script brick (StartScript), else the brick's class.
 */
final class CodeCatalog {
	private static final String RECENTLY_USED = "category_recently_used";

	private CodeCatalog() {
	}

	static String catalog() {
		Project project = new Project();
		Scene scene = new Scene("Scene", project);
		Sprite background = new Sprite("Background");
		Sprite object = new Sprite("Object");
		scene.addSprite(background);
		scene.addSprite(object);
		project.addScene(scene);
		ProjectManager manager = ProjectManager.getInstance();
		manager.setCurrentProject(project);
		manager.setCurrentlyEditedScene(scene);
		manager.setCurrentSprite(object);
		manager.setCurrentlyEditedScene(scene);
		CodeSymbols symbols = new CodeSymbols(project);
		symbols.enterSprite(object);

		CategoryBricksFactory factory = new CategoryBricksFactory();
		CodeExport.Json json = new CodeExport.Json().open('{').key("categories").open('[');
		for (String name : categories()) {
			String key = name.substring(3, name.length() - 1); // "\x01s:category_motion\x02"
			if (RECENTLY_USED.equals(key)) {
				continue;
			}
			List<Brick> bricks = new ArrayList<>();
			Set<String> types = new HashSet<>();
			try {
				// An object's bricks, then those only the background has (as the app's picker shows them for it)
				for (boolean isBackground : new boolean[] {false, true}) {
					for (Brick brick : factory.getBricks(name, isBackground, AllBricksContext.INSTANCE)) {
						if (types.add(CodeExport.type(brick))) {
							bricks.add(brick);
						}
					}
				}
			} catch (Throwable e) {
				json.open('{').key("key").value(key).key("name").value(name).key("bricks").open('[').close(']')
						.key("error").value(e.getClass().getSimpleName() + ": " + e.getMessage()).close('}');
				continue;
			}
			if (bricks.isEmpty()) {
				continue; // a category_* string that is no category of the factory's (icon size, search)
			}
			json.open('{').key("key").value(key).key("name").value(name);
			CodeExport.bricks(json, "bricks", bricks, symbols);
			json.close('}');
		}
		json.close(']').key("formula").open('{');
		elements(json, "functions", Functions.values());
		elements(json, "sensors", Sensors.values());
		elements(json, "operators", Operators.values());
		return json.close('}').close('}').toString();
	}

	private static void elements(CodeExport.Json json, String key, Enum<?>[] values) {
		json.key(key).open('[');
		for (Enum<?> value : values) {
			String label = null;
			try {
				label = WebContext.INSTANCE.getString(InternToExternGenerator.getMappedString(value.name()));
			} catch (RuntimeException e) {
				// not in the formula editor's map: it shows no name for it
			}
			json.open('{').key("name").value(value.name()).key("label").value(label).close('}');
		}
		json.close(']');
	}

	/** The category_* string markers of Catroid's resources (export_code_resources.py), sorted by name. */
	private static List<String> categories() {
		List<String> names = new ArrayList<>(Arrays.asList(categoryMarkers().split("\n")));
		names.removeIf(String::isEmpty);
		names.sort(null);
		return names;
	}

	@JSBody(script = "var s = globalThis.catrobatStrings, out = [];"
			+ "for (var id in s) { if (s[id].indexOf('\\u0001s:category_') === 0) out.push(s[id]); }"
			+ "return out.join('\\n');")
	private static native String categoryMarkers();

	/** The app's context with every brick set switched on in the settings (SettingsFragment reads them). */
	static final class AllBricksContext extends ContextWrapper {
		static final AllBricksContext INSTANCE = new AllBricksContext();

		private AllBricksContext() {
			super(null);
		}

		@Override
		public String getString(int id) {
			return WebContext.INSTANCE.getString(id);
		}

		@Override
		public String getString(int id, Object... formatArgs) {
			return WebContext.INSTANCE.getString(id, formatArgs);
		}

		@Override
		public CharSequence getText(int id) {
			return WebContext.INSTANCE.getText(id);
		}

		@Override
		public android.content.res.Resources getResources() {
			return WebContext.INSTANCE.getResources();
		}

		@Override
		public String getPackageName() {
			return WebContext.INSTANCE.getPackageName();
		}

		@Override
		public SharedPreferences getSharedPreferences(String name, int mode) {
			return AllEnabled.INSTANCE;
		}
	}

	/** Every setting switched on, every other one at its default. */
	static final class AllEnabled implements SharedPreferences {
		static final AllEnabled INSTANCE = new AllEnabled();

		@Override
		public Map<String, ?> getAll() {
			return Map.of();
		}

		@Override
		public String getString(String key, String defValue) {
			return defValue;
		}

		@Override
		public Set<String> getStringSet(String key, Set<String> defValues) {
			return defValues;
		}

		@Override
		public int getInt(String key, int defValue) {
			return defValue;
		}

		@Override
		public long getLong(String key, long defValue) {
			return defValue;
		}

		@Override
		public float getFloat(String key, float defValue) {
			return defValue;
		}

		@Override
		public boolean getBoolean(String key, boolean defValue) {
			return true;
		}

		@Override
		public boolean contains(String key) {
			return false;
		}

		@Override
		public Editor edit() {
			throw new UnsupportedOperationException("the catalog's settings are read-only");
		}

		@Override
		public void registerOnSharedPreferenceChangeListener(OnSharedPreferenceChangeListener listener) {
		}

		@Override
		public void unregisterOnSharedPreferenceChangeListener(OnSharedPreferenceChangeListener listener) {
		}
	}
}
