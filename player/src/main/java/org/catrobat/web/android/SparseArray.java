package org.catrobat.web.android;

import java.util.TreeMap;

/** android.util.SparseArray for the browser (compat/Rewrite remaps it): replaces the android.jar stub, which keeps no values. */
public class SparseArray<E> {
	private final TreeMap<Integer, E> entries = new TreeMap<>();

	public SparseArray() {
	}

	public SparseArray(int initialCapacity) {
	}

	public E get(int key) {
		return entries.get(key);
	}

	public E get(int key, E valueIfKeyNotFound) {
		return entries.getOrDefault(key, valueIfKeyNotFound);
	}

	public void put(int key, E value) {
		entries.put(key, value);
	}

	public void remove(int key) {
		entries.remove(key);
	}

	public void delete(int key) {
		entries.remove(key);
	}

	/** The key's position among the sorted keys, or a negative number if it is absent. */
	public int indexOfKey(int key) {
		return entries.containsKey(key) ? entries.headMap(key).size() : -entries.headMap(key).size() - 1;
	}

	public int size() {
		return entries.size();
	}

	public void clear() {
		entries.clear();
	}
}
