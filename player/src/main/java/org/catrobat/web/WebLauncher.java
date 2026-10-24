package org.catrobat.web;

import com.badlogic.gdx.Gdx;
import com.github.xpenatan.gdx.teavm.backends.web.WebApplication;
import com.github.xpenatan.gdx.teavm.backends.web.WebApplicationConfiguration;

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
import org.catrobat.catroid.stage.StageActivity;
import org.catrobat.catroid.stage.StageListener;
import org.catrobat.catroid.utils.Resolution;
import org.catrobat.web.loader.LoaderCheck;
import org.catrobat.web.loader.ProjectLoader;
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
		if ("check".equals(projectId)) {
			LoaderCheck.run();
			return;
		}
		Project project = projectId.isEmpty() ? demoProject() : loadAndReport(projectId);
		projectManager.setCurrentProject(project);
		projectManager.setCurrentlyPlayingScene(project.getDefaultScene());
		ScreenValues.currentScreenResolution = new Resolution(480, 800);

		WebApplicationConfiguration config = new WebApplicationConfiguration();
		config.width = 480;
		config.height = 800;
		config.preserveDrawingBuffer = true; // lets tests read the stage pixels
		config.alpha = false; // Catroid clears with alpha 0, which Android ignores
		StageListener stage = new StageListener() {
			@Override
			public void create() {
				// A fresh Android GL context clears to transparent; the pen and plot buffers rely on it.
				Gdx.gl.glClearColor(0f, 0f, 0f, 0f);
				super.create();
				setPaused(false); // StageLifeCycleController resumes the stage on Android once resources are ready
			}
		};
		stage.setMaxViewPort(new Resolution(480, 800)); // StageActivity's job on Android
		StageActivity.stageListener = stage; // actors reach the stage through it
		new WebApplication(stage, config);
	}

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
