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

public class WebLauncher {
	public static void main(String[] args) {
		ActionRegistry.registerAll();
		ProjectManager projectManager;
		try {
			projectManager = new ProjectManager(null);
		} catch (RuntimeException e) {
			// The constructor registers the instance, then loads the device's downloaded-project list, which
			// does not exist on the web.
			projectManager = ProjectManager.getInstance();
		}

		Project project = demoProject();
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

	/** Until the project loader exists: a pen that draws a spiral, built from Catroid's own bricks. */
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
