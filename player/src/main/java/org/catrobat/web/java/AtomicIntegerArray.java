package org.catrobat.web.java;

/** java.util.concurrent.atomic.AtomicIntegerArray for the browser, like {@link AtomicReferenceArray}. */
public class AtomicIntegerArray {
	private final int[] array;

	public AtomicIntegerArray(int length) {
		array = new int[length];
	}

	public final int get(int i) {
		return array[i];
	}

	public final boolean compareAndSet(int i, int expected, int value) {
		if (array[i] != expected) {
			return false;
		}
		array[i] = value;
		return true;
	}
}
