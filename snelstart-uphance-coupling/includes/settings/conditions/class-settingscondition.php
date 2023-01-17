<?php


abstract class SettingsCondition {
	public abstract function holds( Settings $settings ): bool;
}