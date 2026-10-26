package org.catrobat.web.codeview;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import org.teavm.jso.JSBody;
import org.teavm.jso.JSObject;

/**
 * Catroid's LayoutInflater.from(context) on the web (WebCompat.layoutInflater): builds CodeViews from the brick
 * layouts that scripts/export_code_resources.py exported, so a brick's getView fills them as on Android.
 */
public final class CodeLayoutInflater extends LayoutInflater {
	private final Context context;

	public CodeLayoutInflater(Context context) {
		super(context);
		this.context = context;
	}

	@Override
	public LayoutInflater cloneInContext(Context newContext) {
		return new CodeLayoutInflater(newContext);
	}

	@Override
	public Context getContext() {
		return context;
	}

	@Override
	public View inflate(int resource, ViewGroup root) {
		return inflateLayout(resource, root, null != root);
	}

	@Override
	public View inflate(int resource, ViewGroup root, boolean attachToRoot) {
		return inflateLayout(resource, root, attachToRoot);
	}

	// Its own name: calling inflate would make javac look at the XmlPullParser overloads, outside the classpath.
	private View inflateLayout(int resource, ViewGroup root, boolean attachToRoot) {
		JSObject layout = layout(resource);
		if (null == layout) {
			// Not a brick layout (e.g. a dialog's): nothing of it is part of a brick's text.
			CodeExport.missingLayout(resource);
			return new CodeViews.Group(context, "");
		}
		View view = build(layout);
		if (attachToRoot && root != null) {
			root.addView(view);
			return root;
		}
		return view;
	}

	private View build(JSObject node) {
		String type = string(node, "t");
		String style = null == string(node, "s") ? "" : string(node, "s");
		View view = switch (type) {
			case "Group", "BrickLayout" -> {
				android.view.ViewGroup group = "BrickLayout".equals(type) ? new CodeViews.BrickGroup(context, style)
						: new CodeViews.Group(context, style);
				for (int i = 0; i < childCount(node); i++) {
					group.addView(build(child(node, i)));
				}
				yield group;
			}
			case "Text" -> new CodeViews.Text(context, style);
			case "Spinner" -> new CodeViews.Spinner(context);
			case "CheckBox" -> new CodeViews.Check(context);
			case "RadioButton" -> new CodeViews.Radio(context);
			case "Category" -> new CodeViews.Leaf(context, "Category." + style);
			default -> new CodeViews.Leaf(context, style);
		};
		if (has(node, "i")) {
			view.setId(integer(node, "i"));
		}
		if (has(node, "g")) {
			view.setVisibility(View.GONE);
		}
		if (view instanceof CodeViews.Text text) {
			if (has(node, "x")) {
				text.setText(context.getString(integer(node, "x")));
			} else if (has(node, "l")) {
				text.setText(string(node, "l"));
			}
		}
		return view;
	}

	@JSBody(params = "id", script = "var l = globalThis.catrobatCode && globalThis.catrobatCode.layouts[id];"
			+ "return l === undefined ? null : l;")
	private static native JSObject layout(int id);

	@JSBody(params = {"node", "key"}, script = "var v = node[key]; return v === undefined ? null : String(v);")
	private static native String string(JSObject node, String key);

	@JSBody(params = {"node", "key"}, script = "return node[key] | 0;")
	private static native int integer(JSObject node, String key);

	@JSBody(params = {"node", "key"}, script = "return node[key] !== undefined;")
	private static native boolean has(JSObject node, String key);

	@JSBody(params = "node", script = "return node.c ? node.c.length : 0;")
	private static native int childCount(JSObject node);

	@JSBody(params = {"node", "i"}, script = "return node.c[i];")
	private static native JSObject child(JSObject node, int i);
}
