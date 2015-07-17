<?php

namespace App\Models;

use App\Models\HierarchicalModel;
use App\Models\Survey;
/**
 * Represents a survey question
 * Properties:
 *  description ::string  contains the question itself as a text
 */
class Question extends HierarchicalModel
{
    //
  //table´s name
  protected $table = 'questions';
  //name of the root class of the hierachy
  protected $stiBaseClass = "App\\Models\\Question";

  public $fillable = array("description","survey_id");

  public function getDescription()
  {
    return $this->description;
  }

  public function setDescription($description)
  {
    $this->description = $description;
  }

  public function getSurvey()
  {
    return $this->survey()->get();
  }

  public function setSurvey($survey)
  {
    return $this->survey()->associate($survey);
  }

  public function survey()
  {
    return $this->belongsTo("App/Models/Survey");
  }

  public function options(){return [];}

  /**
   * Returns the name of the template that will be rendered in the view
   */
  public function getTemplateName()
  {
    return 'pages.surveys.templates.textQuestion';
  }
}
