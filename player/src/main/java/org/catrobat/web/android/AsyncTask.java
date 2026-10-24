package org.catrobat.web.android;

/**
 * android.os.AsyncTask for the browser (compat/Rewrite remaps it): the browser has one thread, so execute runs the
 * task in place. Catroid's read-from-device bricks use it; their actions wait until onPostExecute.
 */
public abstract class AsyncTask<Params, Progress, Result> {
	private boolean cancelled;

	@SuppressWarnings("unchecked")
	protected abstract Result doInBackground(Params... params);

	protected void onPreExecute() {
	}

	protected void onPostExecute(Result result) {
	}

	@SafeVarargs
	protected final void publishProgress(Progress... values) {
	}

	@SafeVarargs
	public final AsyncTask<Params, Progress, Result> execute(Params... params) {
		onPreExecute();
		Result result = doInBackground(params);
		if (!cancelled) {
			onPostExecute(result);
		}
		return this;
	}

	public final boolean cancel(boolean mayInterruptIfRunning) {
		cancelled = true;
		return true;
	}

	public final boolean isCancelled() {
		return cancelled;
	}
}
