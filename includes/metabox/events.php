<?php

return array(

	/**
	 * Event Date
	 */
    array(
        'label'			=> __( 'Event Date', 'uberpress-events' ),
        'description'   => __( 'The date of the event.', 'uberpress-competitions' ),
        'name'			=> 'date',
        'type'			=> 'date'
    ),



);

$prizeFields = array(

    array(
        'label'         => __( 'Type', 'uberpress-competitions' ),
        'description'   => __( 'Select the type of this prize.', 'uberpress-competitions' ),
        'name'          => 'type',
        'type'          => 'radiobutton',
        'default'       => 'custom',
        'items'         => array(
            array(
                'label' => __( 'Custom', 'uberpress-competitions' ),
                'value' => 'custom'
            ),
            array(
                'label' => __( 'Voucher', 'uberpress-competitions' ),
                'value' => 'voucher'
            )
        )
    ),

    array(
        'label'			=> __( 'Amount', 'uberpress-competitions' ),
        'description'	=> __( 'Voucher amount', 'uberpress-competitions' ),
        'name'			=> 'amount',
        'type'			=> 'textbox',
        'dependency'	=> array(
            'field'		=> 'type',
            'function'	=> 'up_competition_p_is_voucher'
        )
    ),

    array(
        'label'			=> __( 'Prize', 'uberpress-competitions' ),
        'description'	=> __( 'Prize description.', 'uberpress-competitions' ),
        'name'			=> 'description',
        'type'			=> 'textbox',
        'dependency'	=> array(
            'field'		=> 'type',
            'function'	=> 'up_competition_p_is_custom'
        )
    )

);

return array(

    // Listing Type
    array(
        'label'         => __( 'Type', 'uberpress-competitions' ),
        'description'   => __( 'Select the Type of this competition.', 'uberpress-competitions' ),
        'name'          => 'type',
        'type'          => 'radiobutton',
        'default'       => 'open',
        'items'         => array(
            array(
                'label' => __( 'Open', 'uberpress-competitions' ),
                'value' => 'open'
            ),
            array(
                'label' => __( 'Question', 'uberpress-competitions' ),
                'value' => 'question'
            )
        )
    ),

    array(
        'label'         => __( 'Mode', 'uberpress-competitions' ),
        'description'   => __( 'Select the mode.', 'uberpress-competitions' ),
        'name'          => 'mode',
        'type'          => 'radiobutton',
        'default'       => 'auto',
        'items'         => array(
            array(
                'label' => __( 'Automatically', 'uberpress-competitions' ),
                'value' => 'auto'
            ),
            array(
                'label' => __( 'Manually', 'uberpress-competitions' ),
                'value' => 'manual'
            )
        )
    ),

    array(

        'title'		=> __( 'Question', 'uberpress-competitions' ),
        'name'		=> 'section_question',
        'type'		=> 'group',
        'dependency'	=> array(
            'field'		=> 'type',
            'function'	=> 'up_competition_is_question'
        ),
        'fields' => array(

            array(
                'label'			=> __( 'Question', 'uberpress-competitions' ),
                'description'	=> __( 'Specify the question.', 'uberpress-competitions' ),
                'name'			=> 'question',
                'type'			=> 'textbox'
            ),

            array(
                'title'			=> __( 'Answer', 'uberpress-competitions' ),
				'title_field'	=> 'answer',
                'name'			=> 'answers',
                'type'			=> 'group',
                'repeating'		=> true,
                'sortable'		=> true,
                'fields'		=> array(

                    array(
                        'label'			=> __( 'Answer', 'uberpress-competitions' ),
                        'description'	=> __( 'Specify an answer text.', 'uberpress-competitions' ),
                        'name'			=> 'answer',
                        'type'			=> 'textbox'
                    ),

                    array(
                        'label'			=> __( 'Correct', 'uberpress-competitions' ),
                        'description'	=> __( 'Whether this answer is a correct one.', 'uberpress-competitions' ),
                        'name'			=> 'correct',
                        'type'			=> 'toggle'
                    )

                )

            )

        )

    ),

    array(
        'label'			=> __( 'End Date', 'uberpress-competitions' ),
        'name'			=> 'ends',
        'type'			=> 'date'
    ),

    array(
        'label'			=> __( 'Terms', 'uberpress-competitions' ),
        'name'			=> 'terms',
        'type'			=> 'textarea'
    ),

    array(
        'title'			=> __( 'Prizes', 'uberpress-competitions' ),
        'name'			=> 'prizes',
        'type'			=> 'group',
        'repeating'		=> true,
		'sortable'		=> true,
        'title_field'	=> 'description',
        'fields'		=> $prizeFields
    ),

    array(
        'title'			=> __( 'Solatium', 'uberpress-competitions' ),
        'name'			=> 'solatium',
        'type'			=> 'group',
        'repeating'		=> false,
        'fields'		=> $prizeFields
    )

);
