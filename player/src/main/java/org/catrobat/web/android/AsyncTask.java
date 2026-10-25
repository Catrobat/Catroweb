package org.catrobat.web.android;

/**
 * android.os.AsyncTask for the browser (compat/Rewrite remaps it): the browser has one thread, so execute runs the
 * task in place. Catroid's read-from-device bricks use it; their actions wait until onPostExecute. Only what
 * Catroid calls on the stage is here (Rewrite stubs the Scratch converter's cancel).
 */
public abstract class AsyncTask<Params, Progress, Result> {
	@SuppressWarnings("unchecked")
	protected abstract Result doInBackground(Params... params);

	protected void onPreExecute() {
	}

	/** Android's no-op default; the read-from-device tasks override it. */
	protected void onPostExecute(Result result) {
	}

	@SafeVarargs
	public final AsyncTask<Params, Progress, Result> execute(Params... params) {
		onPreExecute();
		onPostExecute(doInBackground(params));
		return this;
	}
}
