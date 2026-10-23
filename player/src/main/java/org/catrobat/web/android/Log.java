package org.catrobat.web.android;

/** android.util.Log for the browser (compat/Rewrite remaps it): everything goes to the console. */
public final class Log {
	public static final int VERBOSE = 2;
	public static final int DEBUG = 3;
	public static final int INFO = 4;
	public static final int WARN = 5;
	public static final int ERROR = 6;
	public static final int ASSERT = 7;

	private Log() {
	}

	private static int print(String level, String tag, String msg, Throwable tr) {
		String line = level + "/" + tag + ": " + msg + (null == tr ? "" : " " + tr);
		if ("E".equals(level) || "W".equals(level)) {
			System.err.println(line);
		} else {
			System.out.println(line);
		}
		return line.length();
	}

	public static int v(String tag, String msg) {
		return print("V", tag, msg, null);
	}

	public static int v(String tag, String msg, Throwable tr) {
		return print("V", tag, msg, tr);
	}

	public static int d(String tag, String msg) {
		return print("D", tag, msg, null);
	}

	public static int d(String tag, String msg, Throwable tr) {
		return print("D", tag, msg, tr);
	}

	public static int i(String tag, String msg) {
		return print("I", tag, msg, null);
	}

	public static int i(String tag, String msg, Throwable tr) {
		return print("I", tag, msg, tr);
	}

	public static int w(String tag, String msg) {
		return print("W", tag, msg, null);
	}

	public static int w(String tag, String msg, Throwable tr) {
		return print("W", tag, msg, tr);
	}

	public static int w(String tag, Throwable tr) {
		return print("W", tag, "", tr);
	}

	public static int e(String tag, String msg) {
		return print("E", tag, msg, null);
	}

	public static int e(String tag, String msg, Throwable tr) {
		return print("E", tag, msg, tr);
	}

	public static int wtf(String tag, String msg) {
		return print("E", tag, msg, null);
	}

	public static int wtf(String tag, String msg, Throwable tr) {
		return print("E", tag, msg, tr);
	}

	public static int wtf(String tag, Throwable tr) {
		return print("E", tag, "", tr);
	}

	public static int println(int priority, String tag, String msg) {
		return print(String.valueOf(priority), tag, msg, null);
	}

	public static boolean isLoggable(String tag, int level) {
		return level >= INFO;
	}

	public static String getStackTraceString(Throwable tr) {
		return null == tr ? "" : tr.toString();
	}
}
