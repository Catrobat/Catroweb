package org.catrobat.web.android;

import java.util.Arrays;

/**
 * android.util.SparseIntArray for the browser (compat/Rewrite remaps it): replaces the android.jar stub, which keeps
 * no values, so Catroid's TouchUtil saw every pointer as already down. Keys stay sorted, as on Android, so indexes match.
 */
public class SparseIntArray {
	private int[] keys = new int[0];
	private int[] values = new int[0];

	public SparseIntArray() {
	}

	public SparseIntArray(int initialCapacity) {
	}

	public int get(int key) {
		return get(key, 0);
	}

	public int get(int key, int valueIfKeyNotFound) {
		int index = indexOfKey(key);
		return index >= 0 ? values[index] : valueIfKeyNotFound;
	}

	public void put(int key, int value) {
		int index = indexOfKey(key);
		if (index >= 0) {
			values[index] = value;
			return;
		}
		index = -index - 1;
		keys = insert(keys, index, key);
		values = insert(values, index, value);
	}

	public void delete(int key) {
		int index = indexOfKey(key);
		if (index >= 0) {
			keys = remove(keys, index);
			values = remove(values, index);
		}
	}

	/** The key's position, or a negative number if it is absent. */
	public int indexOfKey(int key) {
		return Arrays.binarySearch(keys, key);
	}

	public int size() {
		return keys.length;
	}

	public int keyAt(int index) {
		return keys[index];
	}

	public int valueAt(int index) {
		return values[index];
	}

	public void clear() {
		keys = new int[0];
		values = new int[0];
	}

	private static int[] insert(int[] array, int index, int value) {
		int[] result = new int[array.length + 1];
		System.arraycopy(array, 0, result, 0, index);
		result[index] = value;
		System.arraycopy(array, index, result, index + 1, array.length - index);
		return result;
	}

	private static int[] remove(int[] array, int index) {
		int[] result = new int[array.length - 1];
		System.arraycopy(array, 0, result, 0, index);
		System.arraycopy(array, index + 1, result, index, result.length - index);
		return result;
	}
}
