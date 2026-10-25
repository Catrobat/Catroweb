package org.catrobat.web;

import com.badlogic.gdx.Gdx;
import com.github.xpenatan.gdx.teavm.backends.web.WebApplication;
import com.github.xpenatan.gdx.teavm.backends.web.WebApplicationConfiguration;
import com.github.xpenatan.gdx.teavm.backends.web.WebFiles;
import com.github.xpenatan.gdx.teavm.backends.web.filesystem.MemoryFileStorage;

import org.catrobat.catroid.ProjectManager;
import org.catrobat.catroid.common.ScreenValues;
import org.catrobat.catroid.content.Project;
import org.catrobat.catroid.content.Scene;
import org.catrobat.catroid.content.Sprite;
import org.catrobat.catroid.content.StartScript;
import org.catrobat.catroid.content.bricks.ForeverBrick;
import org.catrobat.catroid.content.bricks.MoveNStepsBrick;
import org.catrobat.catroid.content.bricks.PenDownBrick;
import org.catrobat.catroid.content.bricks.TurnRightBrick;
import org.catrobat.catroid.formulaeditor.SensorHandler;
import org.catrobat.catroid.stage.StageActivity;
import org.catrobat.catroid.stage.StageListener;
import org.catrobat.catroid.utils.Resolution;
import org.catrobat.web.android.WebContext;
import org.catrobat.web.loader.LoaderCheck;
import org.catrobat.web.loader.ProjectLoader;
import org.catrobat.web.loader.Sounds;
import org.teavm.jso.JSBody;
import org.teavm.jso.browser.Window;

public class WebLauncher {
	public static void main(String[] args) {
		// XStream sniffs the JVM from these; TeaVM leaves them unset.
		for (String key : new String[] {"java.vm.vendor", "java.vm.name", "java.vendor", "java.specification.vendor",
				"java.specification.name"}) {
			System.setProperty(key, "TeaVM");
		}
		System.setProperty("java.specification.version", "21");
		ActionRegistry.registerAll();
		SensorHandler.getInstance(WebContext.INSTANCE); // StageResourceHolder's job on Android; sensor formulas read it
		ProjectManager projectManager;
		try {
			projectManager = new ProjectManager(null);
		} catch (RuntimeException e) {
			// The constructor registers the instance, then loads the device's downloaded-project list, which
			// does not exist on the web.
			projectManager = ProjectManager.getInstance();
		}

		// share.catrobat.org/player/index.html#<project id> plays that project; without one, the built-in demo.
		String projectId = Window.current().getLocation().getHash().replace("#", "");
		fitToPhone();
		if ("check".equals(projectId)) {
			LoaderCheck.run();
			return;
		}
		Project project = projectId.isEmpty() ? demoProject() : loadAndReport(projectId);
		if (!projectId.isEmpty()) {
			ProjectLoader.prefetch(project, projectId);
		}
		boolean interactive = !projectId.isEmpty() && !Window.current().getLocation().getSearch().contains("autostart");
		if (!interactive) {
			Sounds.createContext(); // without a click it may stay muted, which the demo and tests do not mind
		} else {
			StartScreen.waitForStart(project.getName(),
					project.getXmlHeader().getVirtualScreenWidth() > project.getXmlHeader().getVirtualScreenHeight());
		}
		if (!projectId.isEmpty()) {
			ProjectLoader.awaitPrefetch();
			ProjectLoader.loadSounds(project, projectId);
			Sounds.awaitDecoded();
		}
		projectManager.setCurrentProject(project);
		projectManager.setCurrentlyPlayingScene(project.getDefaultScene());
		project.getDirectory().mkdirs(); // the device variables and lists bricks save there, in memory for this play
		project.getRequiredResources(); // StageResourceHolder's job on Android; gives physics sprites their action factory
		// The canvas takes the project's aspect ratio, fitted into the window in device pixels (at most 2 per CSS pixel
		// and 1920 on the long side), so the stage is sharp at full screen. The demo and tests keep 480x800 (800x480 for
		// landscape), so their pixel checks do not depend on the window. CSS keeps it fitted when the window changes.
		boolean sized = project.getXmlHeader().getVirtualScreenWidth() > 0 && project.getXmlHeader().getVirtualScreenHeight() > 0;
		int width = sized ? project.getXmlHeader().getVirtualScreenWidth() : 480;
		int height = sized ? project.getXmlHeader().getVirtualScreenHeight() : 800;
		boolean landscape = width > height;
		double pixels = interactive ? Math.min(2, Window.current().getDevicePixelRatio()) : 1;
		float boxWidth = interactive ? (float) (Window.current().getInnerWidth() * pixels) : landscape ? 800 : 480;
		float boxHeight = interactive ? (float) (Window.current().getInnerHeight() * pixels) : landscape ? 480 : 800;
		float scale = Math.min(Math.min(boxWidth / width, boxHeight / height), 1920f / Math.max(width, height));
		Resolution screen = new Resolution(Math.max(1, Math.round(width * scale)), Math.max(1, Math.round(height * scale)));
		ScreenValues.currentScreenResolution = screen;

		WebApplicationConfiguration config = new WebApplicationConfiguration();
		config.width = screen.getWidth();
		config.height = screen.getHeight();
		config.preserveDrawingBuffer = true; // lets tests read the stage pixels
		config.alpha = false; // Catroid clears with alpha 0, which Android ignores
		// gdx-teavm's default keeps the #<project id> in the URL its scripts load from.
		config.baseUrlProvider = () -> {
			String url = Window.current().getLocation().getFullURL().split("[#?]", 2)[0];
			return url.substring(0, url.lastIndexOf('/') + 1);
		};
		StageListener stage = new StageListener() {
			@Override
			public void create() {
				// gdx-teavm keeps local files in IndexedDB and reloads them on every start; looks are per play.
				((WebFiles) Gdx.files).localStorage = new MemoryFileStorage();
				if (!projectId.isEmpty()) {
					ProjectLoader.loadLooks(project, projectId); // needs Gdx.files, which exists from here on
					StartScreen.close();
				}
				// A fresh Android GL context clears to transparent; the pen and plot buffers rely on it.
				Gdx.gl.glClearColor(0f, 0f, 0f, 0f);
				super.create();
				setPaused(false); // StageLifeCycleController resumes the stage on Android once resources are ready
			}
		};
		stage.setMaxViewPort(screen); // StageActivity's job on Android
		StageActivity.stageListener = stage; // actors reach the stage through it
		// No activity on the web. Empty rather than null: the bricks that need one (vibrate, open URL, edit look, ...)
		// check activeStageActivity.get() and then do nothing, as on a device without the feature.
		StageActivity.activeStageActivity = new java.lang.ref.WeakReference<>(null);
		new WebApplication(stage, config);
		fitCanvas(width, height);
	}

	/** gdx-teavm's page has no viewport meta, so phones laid it out 980 px wide and zoomed out. */
	@JSBody(script = "var meta = document.createElement('meta'); meta.name = 'viewport';"
			+ "meta.content = 'width=device-width, initial-scale=1, viewport-fit=cover'; document.head.append(meta);")
	private static native void fitToPhone();

	/** As large as the window allows at the project's aspect ratio; gdx-teavm maps touches through the CSS scaling. */
	@JSBody(params = {"width", "height"}, script = "var style = document.createElement('style');"
			+ "style.textContent = 'body { height: 100vh; height: 100dvh; } #canvas { display: block; height: auto;"
			+ " width: min(100vw, 100vh * ' + width + ' / ' + height + '); width: min(100vw, 100dvh * ' + width + ' / '"
			+ " + height + '); touch-action: none; }'; document.head.append(style);")
	private static native void fitCanvas(int width, int height);

	private static Project loadAndReport(String projectId) {
		Project project = ProjectLoader.load(projectId);
		System.out.println("player: loaded \"" + project.getName() + "\": " + ProjectLoader.summary(project).split(" \\|")[0]);
		return project;
	}

	/** Without a project id: a pen that draws a spiral, built from Catroid's own bricks. */
	private static Project demoProject() {
		Project project = new Project();
		project.setName("Spiral");
		project.getXmlHeader().setVirtualScreenWidth(480);
		project.getXmlHeader().setVirtualScreenHeight(800);
		Scene scene = new Scene("default", project);
		project.addScene(scene);
		scene.addSprite(new Sprite("Background"));

		Sprite pen = new Sprite("Pen");
		StartScript script = new StartScript();
		script.addBrick(new PenDownBrick());
		ForeverBrick forever = new ForeverBrick();
		forever.addBrick(new MoveNStepsBrick(6));
		forever.addBrick(new TurnRightBrick(7));
		script.addBrick(forever);
		pen.addScript(script);
		scene.addSprite(pen);
		return project;
	}
}
