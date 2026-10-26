package org.catrobat.web.codeview;

import android.content.Context;
import android.content.res.Resources;
import android.util.AttributeSet;
import android.view.View;
import android.widget.Adapter;
import android.widget.SpinnerAdapter;

import java.util.ArrayList;
import java.util.List;

/**
 * Views that keep what a brick's own getView puts into them, so that CodeExport can read the brick as the app shows
 * it. They subclass the mockable SDK widgets (whose methods do nothing), so Catroid's casts and calls work as on
 * Android; LayoutInflater builds them from Catroid's layouts (CodeLayoutInflater).
 */
public final class CodeViews {
	private CodeViews() {
	}

	/** What all of them share: an id, visibility, a context and the layout style they came with. */
	interface Recording {
		String style();
	}

	/** A layout: its children in order. */
	interface Parent extends Recording {
		List<View> children();
	}

	/** Every view's background, which bricks tint (spinners, commented-out bricks): a colour that stays as it is. */
	static final android.graphics.drawable.Drawable BACKGROUND = new android.graphics.drawable.ColorDrawable() {
		@Override
		public android.graphics.drawable.Drawable mutate() {
			return this;
		}
	};

	static View find(View view, int id) {
		if (View.NO_ID != id && view.getId() == id) {
			return view;
		}
		if (view instanceof Parent parent) {
			for (View child : parent.children()) {
				View found = find(child, id);
				if (null != found) {
					return found;
				}
			}
		}
		return null;
	}

	/** A LinearLayout (only its children and style matter here). */
	public static final class Group extends android.widget.LinearLayout implements Parent {
		final List<View> children = new ArrayList<>();
		private final Context context;
		private final String style;
		private int id = NO_ID;
		private int visibility = VISIBLE;

		Group(Context context, String style) {
			super(context);
			this.context = context;
			this.style = style;
		}

		@Override
		public String style() {
			return style;
		}

		@Override
		public List<View> children() {
			return children;
		}

		@Override
		public Context getContext() {
			return context;
		}

		@Override
		public Resources getResources() {
			return context.getResources();
		}

		@Override
		public android.graphics.drawable.Drawable getBackground() {
			return BACKGROUND;
		}

		@Override
		public void setId(int id) {
			this.id = id;
		}

		@Override
		public int getId() {
			return id;
		}

		@Override
		public void setVisibility(int visibility) {
			this.visibility = visibility;
		}

		@Override
		public int getVisibility() {
			return visibility;
		}

		@Override
		public void addView(View child) {
			children.add(child);
		}

		@Override
		public void addView(View child, int index) {
			children.add(index < 0 ? children.size() : index, child);
		}

		@Override
		public void addView(View child, android.view.ViewGroup.LayoutParams params) {
			addView(child);
		}

		@Override
		public void addView(View child, int index, android.view.ViewGroup.LayoutParams params) {
			addView(child, index);
		}

		@Override
		public void removeView(View child) {
			children.remove(child);
		}

		@Override
		public void removeAllViews() {
			children.clear();
		}

		@Override
		public int getChildCount() {
			return children.size();
		}

		@Override
		public View getChildAt(int index) {
			return index < children.size() ? children.get(index) : null;
		}

		@Override
		@SuppressWarnings("unchecked")
		public <T extends View> T findViewById(int id) {
			return (T) find(this, id);
		}
	}

	/**
	 * Catroid's BrickLayout, a brick's own layout: of its class, since bricks such as the user-defined ones use it
	 * as one (TeaVM drops calls on a value that cannot be of the class it is cast to).
	 */
	public static final class BrickGroup extends org.catrobat.catroid.ui.BrickLayout implements Parent {
		final List<View> children = new ArrayList<>();
		private final Context context;
		private final String style;
		private int id = NO_ID;
		private int visibility = VISIBLE;

		BrickGroup(Context context, String style) {
			super(context);
			this.context = context;
			this.style = style;
		}

		@Override
		public String style() {
			return style;
		}

		@Override
		public List<View> children() {
			return children;
		}

		@Override
		public Context getContext() {
			return context;
		}

		@Override
		public Resources getResources() {
			return context.getResources();
		}

		@Override
		public android.graphics.drawable.Drawable getBackground() {
			return BACKGROUND;
		}

		@Override
		public void setId(int id) {
			this.id = id;
		}

		@Override
		public int getId() {
			return id;
		}

		@Override
		public void setVisibility(int visibility) {
			this.visibility = visibility;
		}

		@Override
		public int getVisibility() {
			return visibility;
		}

		@Override
		public void addView(View child) {
			children.add(child);
		}

		@Override
		public void addView(View child, int index) {
			children.add(index < 0 ? children.size() : index, child);
		}

		@Override
		public void addView(View child, android.view.ViewGroup.LayoutParams params) {
			addView(child);
		}

		@Override
		public void addView(View child, int index, android.view.ViewGroup.LayoutParams params) {
			addView(child, index);
		}

		@Override
		public void removeView(View child) {
			children.remove(child);
		}

		@Override
		public void removeAllViews() {
			children.clear();
		}

		@Override
		public int getChildCount() {
			return children.size();
		}

		@Override
		public View getChildAt(int index) {
			return index < children.size() ? children.get(index) : null;
		}

		@Override
		@SuppressWarnings("unchecked")
		public <T extends View> T findViewById(int id) {
			return (T) find(this, id);
		}
	}

	/**
	 * Every TextView: the layouts' and those bricks create themselves (Rewrite substitutes it for TextView in
	 * Catroid's code). A user-defined brick's input fields are created with the BrickEditText style.
	 */
	public static class Text extends android.widget.TextView implements Recording {
		private final Context context;
		private String style;
		private CharSequence text = "";
		private android.view.ViewGroup.LayoutParams layoutParams;
		private int id = NO_ID;
		private int visibility = VISIBLE;

		public Text(Context context) {
			super(context);
			this.context = context;
			this.style = "";
		}

		public Text(Context context, AttributeSet attributes) {
			this(context);
		}

		public Text(Context context, AttributeSet attributes, int defStyleAttr) {
			this(context);
		}

		public Text(Context context, AttributeSet attributes, int defStyleAttr, int defStyleRes) {
			this(context);
			if (defStyleRes == org.catrobat.catroid.R.style.BrickEditText) {
				style = "BrickEditText";
			}
		}

		Text(Context context, String style) {
			this(context);
			this.style = style;
		}

		@Override
		public String style() {
			return style;
		}

		@Override
		public Context getContext() {
			return context;
		}

		@Override
		public Resources getResources() {
			return context.getResources();
		}

		@Override
		public android.graphics.drawable.Drawable getBackground() {
			return BACKGROUND;
		}

		@Override
		public void setId(int id) {
			this.id = id;
		}

		@Override
		public int getId() {
			return id;
		}

		@Override
		public void setVisibility(int visibility) {
			this.visibility = visibility;
		}

		@Override
		public int getVisibility() {
			return visibility;
		}

		@Override
		public void setText(CharSequence text) {
			this.text = null == text ? "" : text;
		}

		@Override
		public void setText(CharSequence text, BufferType type) {
			setText(text);
		}

		@Override
		public void setText(int resId) {
			setText(context.getString(resId));
		}

		@Override
		public CharSequence getText() {
			return text;
		}

		@Override
		public float getTextSize() {
			return 14;
		}

		/** A user-defined brick's getView sets its spacing in BrickLayout's parameters. */
		@Override
		public android.view.ViewGroup.LayoutParams getLayoutParams() {
			if (null == layoutParams) {
				layoutParams = new org.catrobat.catroid.ui.BrickLayout.LayoutParams(android.view.ViewGroup.LayoutParams.WRAP_CONTENT,
						android.view.ViewGroup.LayoutParams.WRAP_CONTENT);
			}
			return layoutParams;
		}

		@Override
		@SuppressWarnings("unchecked")
		public <T extends View> T findViewById(int id) {
			return (T) find(this, id);
		}
	}

	/** A spinner shows the name of its selected item. */
	public static final class Spinner extends android.widget.Spinner implements Recording {
		private final Context context;
		private SpinnerAdapter adapter;
		private int selection;
		private int id = NO_ID;
		private int visibility = VISIBLE;

		Spinner(Context context) {
			super(context);
			this.context = context;
		}

		@Override
		public String style() {
			return "Spinner";
		}

		@Override
		public Context getContext() {
			return context;
		}

		@Override
		public Resources getResources() {
			return context.getResources();
		}

		@Override
		public android.graphics.drawable.Drawable getBackground() {
			return BACKGROUND;
		}

		@Override
		public void setId(int id) {
			this.id = id;
		}

		@Override
		public int getId() {
			return id;
		}

		@Override
		public void setVisibility(int visibility) {
			this.visibility = visibility;
		}

		@Override
		public int getVisibility() {
			return visibility;
		}

		@Override
		public void setAdapter(SpinnerAdapter adapter) {
			this.adapter = adapter;
		}

		@Override
		public SpinnerAdapter getAdapter() {
			return adapter;
		}

		@Override
		public void setSelection(int position) {
			selection = position;
		}

		@Override
		public void setSelection(int position, boolean animate) {
			selection = position;
		}

		@Override
		public int getSelectedItemPosition() {
			return selection;
		}

		@Override
		public Object getSelectedItem() {
			return null == adapter || selection < 0 || selection >= ((Adapter) adapter).getCount() ? null
					: ((Adapter) adapter).getItem(selection);
		}

		@Override
		@SuppressWarnings("unchecked")
		public <T extends View> T findViewById(int id) {
			return (T) find(this, id);
		}
	}

	/** The selection check box every brick layout has: not part of the text. */
	public static final class Check extends android.widget.CheckBox implements Recording {
		private final Context context;
		private int id = NO_ID;
		private int visibility = VISIBLE;

		Check(Context context) {
			super(context);
			this.context = context;
		}

		@Override
		public String style() {
			return "Check";
		}

		@Override
		public Context getContext() {
			return context;
		}

		@Override
		public Resources getResources() {
			return context.getResources();
		}

		@Override
		public android.graphics.drawable.Drawable getBackground() {
			return BACKGROUND;
		}

		@Override
		public void setId(int id) {
			this.id = id;
		}

		@Override
		public int getId() {
			return id;
		}

		@Override
		public void setVisibility(int visibility) {
			this.visibility = visibility;
		}

		@Override
		public int getVisibility() {
			return visibility;
		}
	}

	/** The Phiro bricks' radio buttons: not part of the text either. */
	public static final class Radio extends android.widget.RadioButton implements Recording {
		private final Context context;
		private int id = NO_ID;
		private int visibility = VISIBLE;

		Radio(Context context) {
			super(context);
			this.context = context;
		}

		@Override
		public String style() {
			return "Radio";
		}

		@Override
		public Context getContext() {
			return context;
		}

		@Override
		public Resources getResources() {
			return context.getResources();
		}

		@Override
		public android.graphics.drawable.Drawable getBackground() {
			return BACKGROUND;
		}

		@Override
		public void setId(int id) {
			this.id = id;
		}

		@Override
		public int getId() {
			return id;
		}

		@Override
		public void setVisibility(int visibility) {
			this.visibility = visibility;
		}

		@Override
		public int getVisibility() {
			return visibility;
		}
	}

	/** Anything else, such as the category icon, which names the brick's category. */
	public static final class Leaf extends View implements Recording {
		private final Context context;
		private final String style;
		private int id = NO_ID;
		private int visibility = VISIBLE;

		Leaf(Context context, String style) {
			super(context);
			this.context = context;
			this.style = style;
		}

		@Override
		public String style() {
			return style;
		}

		@Override
		public Context getContext() {
			return context;
		}

		@Override
		public Resources getResources() {
			return context.getResources();
		}

		@Override
		public android.graphics.drawable.Drawable getBackground() {
			return BACKGROUND;
		}

		@Override
		public void setId(int id) {
			this.id = id;
		}

		@Override
		public int getId() {
			return id;
		}

		@Override
		public void setVisibility(int visibility) {
			this.visibility = visibility;
		}

		@Override
		public int getVisibility() {
			return visibility;
		}
	}
}
