package org.catrobat.web.java;

import java.util.concurrent.TimeUnit;

/** java.util.concurrent.locks.Lock, which TeaVM's class library lacks (compat/Rewrite remaps it). */
public interface Lock {
	void lock();

	void lockInterruptibly() throws InterruptedException;

	boolean tryLock();

	boolean tryLock(long time, TimeUnit unit) throws InterruptedException;

	void unlock();

	Object newCondition();
}
