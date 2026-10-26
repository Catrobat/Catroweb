package org.catrobat.web.codeview;

import android.content.Context;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collection;
import java.util.List;

/**
 * The SDK's ArrayAdapter backed by its list: Rewrite substitutes it for ArrayAdapter in Catroid's code, so the
 * brick spinners' items (BrickSpinner.BrickSpinnerAdapter) are there for CodeViews.Spinner to read.
 */
public class CodeArrayAdapter<T> extends ArrayAdapter<T> {
	private final Context context;
	private final List<T> objects;

	public CodeArrayAdapter(Context context, int resource) {
		this(context, resource, 0, new ArrayList<>());
	}

	public CodeArrayAdapter(Context context, int resource, int textViewResourceId) {
		this(context, resource, textViewResourceId, new ArrayList<>());
	}

	public CodeArrayAdapter(Context context, int resource, T[] objects) {
		this(context, resource, 0, new ArrayList<>(Arrays.asList(objects)));
	}

	public CodeArrayAdapter(Context context, int resource, int textViewResourceId, T[] objects) {
		this(context, resource, textViewResourceId, new ArrayList<>(Arrays.asList(objects)));
	}

	public CodeArrayAdapter(Context context, int resource, List<T> objects) {
		this(context, resource, 0, objects);
	}

	public CodeArrayAdapter(Context context, int resource, int textViewResourceId, List<T> objects) {
		super(context, resource, textViewResourceId, objects);
		this.context = context;
		this.objects = objects;
	}

	@Override
	public Context getContext() {
		return context;
	}

	@Override
	public void add(T object) {
		objects.add(object);
	}

	@Override
	public void addAll(Collection<? extends T> collection) {
		objects.addAll(collection);
	}

	@Override
	@SafeVarargs
	public final void addAll(T... items) {
		objects.addAll(Arrays.asList(items));
	}

	@Override
	public void insert(T object, int index) {
		objects.add(index, object);
	}

	@Override
	public void remove(T object) {
		objects.remove(object);
	}

	@Override
	public void clear() {
		objects.clear();
	}

	@Override
	public int getCount() {
		return objects.size();
	}

	@Override
	public T getItem(int position) {
		return position >= 0 && position < objects.size() ? objects.get(position) : null;
	}

	@Override
	public int getPosition(T item) {
		return objects.indexOf(item);
	}

	@Override
	public long getItemId(int position) {
		return position;
	}

	@Override
	public View getView(int position, View convertView, ViewGroup parent) {
		return convertView;
	}

	@Override
	public void notifyDataSetChanged() {
	}
}
