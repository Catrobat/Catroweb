package com.github.xpenatan.gdx.teavm.backends.web;

import com.badlogic.gdx.ApplicationListener;
import com.badlogic.gdx.Gdx;

/**
 * gdx-teavm's start without its two waits, about two seconds in all. In its package for WebGraphics.frameId.
 */
public final class QuickStart {
	private QuickStart() {
	}

	/**
	 * For ApplicationListener.create(): WebApplication renders a listener only from its 61st frame on ("so fps don't
	 * start with 0"), a second of black screen.
	 */
	public static void skipWarmup() {
		((WebGraphics) Gdx.graphics).frameId = 60;
	}

	/** The default preloader, which also fills its progress bar at 0.9 per second before it lets the app start. */
	public static ApplicationListener preloader() {
		WebPreloadApplicationListener preloader = new WebPreloadApplicationListener() {
			@Override
			public void create() {
				super.create();
				skipWarmup();
			}
		};
		preloader.animationSpeed = Float.MAX_VALUE;
		return preloader;
	}
}
