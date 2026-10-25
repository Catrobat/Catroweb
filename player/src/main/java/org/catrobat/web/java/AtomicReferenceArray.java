package org.catrobat.web.java;

/**
 * java.util.concurrent.atomic.AtomicReferenceArray, which TeaVM's class library lacks (compat/Rewrite remaps it), with
 * the calls kotlinx.coroutines makes. The browser runs one thread, so a plain array is atomic.
 */
public class AtomicReferenceArray<E> {
	private final Object[] array;

	public AtomicReferenceArray(int length) {
		array = new Object[length];
	}

	public final int length() {
		return array.length;
	}

	@SuppressWarnings("unchecked")
	public final E get(int i) {
		return (E) array[i];
	}

	public final void set(int i, E value) {
		array[i] = value;
	}

	public final void lazySet(int i, E value) {
		array[i] = value;
	}

	public final E getAndSet(int i, E value) {
		E old = get(i);
		array[i] = value;
		return old;
	}

	public final boolean compareAndSet(int i, E expected, E value) {
		if (array[i] != expected) {
			return false;
		}
		array[i] = value;
		return true;
	}
}
