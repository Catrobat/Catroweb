package org.catrobat.web.java;

import java.util.concurrent.TimeUnit;

/**
 * java.util.concurrent.locks.ReentrantLock for the browser (compat/Rewrite remaps it). TeaVM threads are cooperative
 * on one JavaScript thread, so this only counts nested holds; Catroid uses it to guard loading and saving.
 */
// ponytail: no blocking between TeaVM threads; implement wait/notify if two threads ever contend for one.
public class ReentrantLock implements Lock {
	private int holds;

	public ReentrantLock() {
	}

	public ReentrantLock(boolean fair) {
	}

	@Override
	public void lock() {
		holds++;
	}

	@Override
	public void lockInterruptibly() {
		lock();
	}

	@Override
	public boolean tryLock() {
		lock();
		return true;
	}

	@Override
	public boolean tryLock(long time, TimeUnit unit) {
		return tryLock();
	}

	@Override
	public void unlock() {
		if (holds <= 0) {
			throw new IllegalMonitorStateException();
		}
		holds--;
	}

	@Override
	public Object newCondition() {
		throw new UnsupportedOperationException("Conditions are not available on the web");
	}

	public boolean isLocked() {
		return holds > 0;
	}

	public boolean isHeldByCurrentThread() {
		return isLocked();
	}

	public int getHoldCount() {
		return holds;
	}
}
