<?php

namespace App\Models;

use App\Models\Question;

use stdClass;

/**
 * Represents a question with subquestions with only one answer each.
 */
class MultipleSelectionQuestion extends Question {

    public $with = "subquestions";

    public function subquestions() {
        return $this->hasMany("App\Models\MultipleSelectionSubquestion","question_id");
    }

    public function getSubquestions() {
        return $this->subquestions()->get();
    }

    public function addSubquestion($subquestion) {
        $this->subquestions()->save($subquestion);
    }

    public function getPossibleAnswers() {
        return $this->multiple_selection_answers;
    }

    public function getDecodedPossibleAnswers() {
        return json_decode($this->getPossibleAnswers());
    }

    public function getTemplateName() {
        return 'pages.questionnaires.templates.multipleSelectionQuestion';
    }

    public function getReportTemplateName()
    {
        return 'pages.questionnaires.templates.multipleSelectionQuestionTextReport';
    }

    public function createNewAnswerForMyself($respondent,$answerData) {
        $answers = [];
        foreach ($answerData as $optionId => $answerValue) {
            // Gets the selected option
            $optionSelected = MultipleSelectionOption::find($optionId);
            // Saves the answer
            $answers[] = AnsweredWithSelectionOption::createNewAnswerFor($respondent,$this->getQuestionnaire(),$this,$optionSelected, $answerValue["option"]);
        }

        return $answers;
    }

    public function getReportInformation() {
        $questionInformation = new stdClass();
        $questionInformation->description = $this->getDescription();
        $questionInformation->subquestions = array();
        foreach ($this->getSubquestions() as $key => $subquestion)
        {
            $answersMatrix = $this->getAnswersMatrix();
            $subquestionJson = new stdClass();
            $subquestionJson->description = $subquestion->getDescription();
            $subquestionJson->options = array();
            foreach ($subquestion->getOptions() as $key => $option) {
                $optionJson = new StdClass();
                $optionJson->description=$option->getDescription();
                $optionJson->answersCount = $answersMatrix[$option->id];
                $subquestionJson->options[] = $optionJson;
            }
            $questionInformation->subquestions[] = $subquestionJson;
            $questionInformation->reportTemplate = $this->getReportTemplateName();
            $questionInformation->possibleAnswers = $this->getDecodedPossibleAnswers();

        } ;
        return $questionInformation;

    }
    /**
     * Gets all the answers for this question
     */
    private function getAnswersMatrix()
    {
        $answers = $this->getAnswers();
        $answersMatrix = [];
        foreach ($answers as $key => $answer)
        {
            if(!isset($answersMatrix[$answer->multiple_selection_option_id]))
            {
                $answersMatrix[$answer->multiple_selection_option_id] = $this->getPossibleAnswersCounters();
            }
            $answersMatrix[$answer->multiple_selection_option_id][$answer->answer] +=1;
        }
        return $answersMatrix;
    }

    private function getPossibleAnswersCounters()
    {
        $possibleAnswers = json_decode($this->getPossibleAnswers());
        return  array_reduce($possibleAnswers,
                function($possibleAnswersArray,$nextPossibleAnswer)
                {
                    $possibleAnswersArray[$nextPossibleAnswer->acronym] = 0;
                    return $possibleAnswersArray;
                },[]);

    }

}
