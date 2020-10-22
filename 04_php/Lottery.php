<?php

/**
 * Class Lottery
 */
class Lottery
{

    /**
     * Name of the Lottery Game
     *
     * @var string
     */
    protected string $name;

    /**
     * Default time-zone of the Game
     *
     * @var string
     */
    protected string $defaultTimeZone;

    /**
     * The current Draw Week Number
     *
     * @var string
     */
    protected string $currentWeekNumber;

    /**
     * The current Date Time
     *
     * @var object
     */
    protected object $currentDateTime;

    /**
     * The user time-zone
     *
     * @var string
     */
    protected string $userTimeZone;

    /**
     * Time Zone Offset
     *
     * @var integer
     */
    protected int $userTimeOffset;

    /**
     * Number of drawWeeks
     *
     * @var array
     */
    protected array $drawWeekNumbers = [];

    /**
     * Store the date of the draw dates
     *
     * @var array
     */
    protected array $drawDaysDate = [];

    /**
     * The next draw day
     *
     * @var string
     */
    protected string $nextDrawDate;

    /**
     * The current year
     *
     * @var string
     */
    protected string $currentYear;

    const LOTTERY_NAME = 'The Canadian National Lottery';

    const CANADIAN_TIMEZONE = 'America/Toronto';

    const USER_FALLBACK_TIMEZONE = 'Europe/Budapest';

    const DRAW_DAY_SUNDAY = 'Sunday';

    const DRAW_DAY_TUESDAY = 'Tuesday';

    const DRAW_HOUR = 21;

    const DRAW_MINUTE = 30;

    const DRAW_SECOND = 00;

    const DAYS_OF_A_WEEK = [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
    ];


    /**
     * Lottery constructor.
     */
    public function __construct()
    {
        $this->name            = self::LOTTERY_NAME;
        $this->defaultTimeZone = self::CANADIAN_TIMEZONE;

    }//end __construct()


    /**
     * Set the User TimeZone
     *
     * @param string|null $setTimeZone Explicitly set timeZone.
     *
     * @return null
     */
    protected function setUserTimeZone(string $setTimeZone=null)
    {
        if (empty($setTimeZone) === false) {
            $this->userTimeZone = $setTimeZone;
        } else {
            if (date_default_timezone_get() === true) {
                $this->userTimeZone = date_default_timezone_get();
            } else {
                $this->userTimeZone = self::USER_FALLBACK_TIMEZONE;
            }
        }

        return null;

    }//end setUserTimeZone()


    /**
     * Get User Time Zone
     *
     * @return string
     */
    protected function getUserTimeZone()
    {
        return $this->userTimeZone;

    }//end getUserTimeZone()


    /**
     * Set week and date time
     *
     * @param string $setDate Set custom date explicitly.
     *
     * @return array
     */
    protected function setWeekAndDateTime(string $setDate): array
    {
        try {
            $userTimeZone = new DateTimeZone($this->getUserTimeZone());
            $currentTime  = new DateTime($setDate);
            $currentWeek  = ($currentTime)->format('W');
            $currentYear  = ($currentTime)->format('Y');
            $currentTime->format('Y-m-d H:i:s P e');
            $currentTime->setTimezone($userTimeZone);
            $offset = $currentTime->getOffset();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit(1);
        }

        return [
            'currentYear' => $currentYear,
            'currentTime' => $currentTime,
            'currentWeek' => $currentWeek,
            'offset'      => $offset,
        ];

    }//end setWeekAndDateTime()


    /**
     * We calculate offsets from the different locations.
     *
     * @param string  $drawDateTime   Draw date time.
     * @param integer $userOffset     User offset from GMT.
     * @param integer $canadianOffset Canadian offset from GMT.
     *
     * @return string Offset correction based on location.
     */
    protected function calculateOffsetFromGmt(string $drawDateTime, int $userOffset, int $canadianOffset)
    {
        try {
            $offsetCorrectedDateTime = new DateTime($drawDateTime);
            if ($userOffset >= 0) {
                $difference = abs(abs($canadianOffset) + abs($userOffset));
                $interval   = DateInterval::createFromDateString($difference.'seconds');
                $offsetCorrectedDateTime->add($interval);
            } else {
                $difference = abs(abs($userOffset) - abs($canadianOffset));
                $interval   = DateInterval::createFromDateString($difference.'seconds');
                $offsetCorrectedDateTime->sub($interval);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            exit(1);
        }

        $offsetCorrectedDateTime = $offsetCorrectedDateTime->format('Y-m-d H:i:s');

        return $offsetCorrectedDateTime;

    }//end calculateOffsetFromGmt()


    /**
     * We can explicitly specify the current dateTime.
     *
     * @param string|null $fromThisDateTime Explicit parameter from the datetime.
     *
     * @return $this
     */
    public function getNextDrawDays(string $fromThisDateTime=null)
    {
        $nextDrawDate = '';
        $setDate      = date('Y-m-d H:i:s');
        if (empty($fromThisDateTime) === false) {
            $setDate = $fromThisDateTime;
        }

        $dateRelatedInfo         = $this->setWeekAndDateTime($setDate);
        $this->currentYear       = $dateRelatedInfo['currentYear'];
        $this->currentDateTime   = $dateRelatedInfo['currentTime'];
        $this->currentWeekNumber = $dateRelatedInfo['currentWeek'];
        $this->userTimeOffset    = $dateRelatedInfo['offset'];
        $range                   = range($this->currentWeekNumber, ($this->currentWeekNumber + 2));

        foreach ($range as $this->currentWeekNumber) {
            $defaultTimeZone = new DateTimeZone($this->defaultTimeZone);
            $weekStart       = new DateTime();
            $weekStart->setISODate($this->currentYear, $this->currentWeekNumber);
            $weekStart->setTimezone($defaultTimeZone);
            $weekStart->setTime(self::DRAW_HOUR, self::DRAW_MINUTE, self::DRAW_SECOND);
            $weekStart->modify('-1 day');
            $weekStart->getOffset();
            $week             = [];
            $daysOfWeekLength = count(self::DAYS_OF_A_WEEK);
            for ($i = 0; $i < $daysOfWeekLength; $i++) {
                $day = self::DAYS_OF_A_WEEK[$i];
                if ($day === self::DRAW_DAY_TUESDAY || $day === self::DRAW_DAY_SUNDAY) {
                    if (($weekStart > $this->currentDateTime) === true
                        && empty($nextDrawDate)
                    ) {
                        $drawDateTime   = $weekStart->format('Y-m-d H:i:s');
                        $userOffset     = $this->userTimeOffset;
                        $canadianOffset = $weekStart->getOffset();
                        $drawDate       = $this->calculateOffsetFromGmt(
                            $drawDateTime,
                            $userOffset,
                            $canadianOffset
                        );
                        $nextDrawDate   = $drawDate;
                    }

                    $week[$day] = $weekStart->format('Y-m-d H:i:s');
                }

                $weekStart->modify('+1 day');
            }//end for

            $this->drawWeekNumbers[] = 'Week '.$this->currentWeekNumber;
            $this->drawDaysDate[]    = $week;
        }//end foreach

        $this->nextDrawDate = $nextDrawDate;
        return $this;

    }//end getNextDrawDays()


    /**
     * Get Next Draw Day
     *
     * @param string|null $fromThisDateTime Set date time explicitly.
     * @param string|null $inThisTimeZone   Set time zone explicitly.
     *
     * @return string
     */
    public function getNextDrawDay(
        string $fromThisDateTime=null,
        string $inThisTimeZone=null
    ) {
        $this->setUserTimeZone($inThisTimeZone);
        $nextDrawDays = $this->getNextDrawDays($fromThisDateTime);

        if (isset($nextDrawDays) === true) {
            $result = "..:: The next draw date ::.. \t".$nextDrawDays->nextDrawDate.'-'.$this->getUserTimeZone()."\n";
        } else {
            $result = '';
        }

        return $result;

    }//end getNextDrawDay()


}//end class

$lottery = new Lottery();
echo $lottery->getNextDrawDay();
echo $lottery->getNextDrawDay('2018-01-02 09:30:00', 'Europe/Budapest');
echo $lottery->getNextDrawDay('2018-01-02 09:30:00', 'America/Los_Angeles');
echo $lottery->getNextDrawDay('2018-01-02 14:31:00', 'GMT');
echo $lottery->getNextDrawDay('2018-01-02 09:30:00', 'Asia/Yerevan');
echo $lottery->getNextDrawDay('2020-10-19 21:50:00', 'America/Toronto');
echo $lottery->getNextDrawDay('2020-10-19 21:50:00', 'America/V');
