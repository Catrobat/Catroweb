package org.catrobat.web.android;

/** android.os.SystemClock for the browser (compat/Rewrite remaps it). */
public final class SystemClock {
	private static final long START = System.currentTimeMillis();

	private SystemClock() {
	}

	public static long uptimeMillis() {
		return System.currentTimeMillis() - START;
	}

	public static long elapsedRealtime() {
		return uptimeMillis();
	}

	public static long elapsedRealtimeNanos() {
		return uptimeMillis() * 1_000_000L;
	}

	public static long currentThreadTimeMillis() {
		return uptimeMillis();
	}

	public static void sleep(long ms) {
		try {
			Thread.sleep(ms);
		} catch (InterruptedException ignored) {
			Thread.currentThread().interrupt();
		}
	}
}
