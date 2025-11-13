import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin, { Draggable } from '@fullcalendar/interaction';

// Constants
const CALENDAR_CONFIG = {
  locale: 'de',
  firstDay: 1, // Start week on Monday (0=Sunday, 1=Monday)
  initialView: 'timeGridWeek',
  headerToolbar: {
    left: '',
    center: 'title',
    right: ''
  },
  hiddenDays: [0, 6], // Hide weekends
  slotMinTime: '08:00:00',
  slotMaxTime: '18:00:00',
  businessHours: [
    { daysOfWeek: [1, 2, 3, 4, 5], startTime: '08:00', endTime: '12:00' },
    { daysOfWeek: [1, 2, 3, 4, 5], startTime: '13:00', endTime: '18:00' }
  ],
  eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
  allDaySlot: false,
  height: 'auto',
  longPressDelay: 50,
  eventLongPressDelay: 50,
  selectLongPressDelay: 50
};

const CSS_CLASSES = {
  quizCompleted: 'mc-quiz-completed',
  questionTextCalendar: 'wpProQuiz_question_text-calendar',
  calendar: 'calendar',
  incorrectAnswer: 'wpProQuiz_incorrect',
  answerMessage: 'wpProQuiz_AnswerMessage',
  listItem: 'wpProQuiz_listItem',
  questionList: 'wpProQuiz_questionList',
  matrixSortString: 'wpProQuiz_matrixSortString',
  matrixSortText: 'wpProQuiz_maxtrixSortText', // Keep original typo for compatibility
  matrixSortCriterion: 'wpProQuiz_maxtrixSortCriterion', // Keep original typo for compatibility
  questionListItem: 'wpProQuiz_questionListItem',
  answerCorrect: 'wpProQuiz_answerCorrect',
  externalEvents: 'external-events',
  externalEventsBackup: 'external-events-backup',
  externalEventsContainer: 'external-events-container',
  fcEvent: 'fc-event',
  fcEventMain: 'fc-event-main',
  defaultEvent: 'mc-default-event',
  correctAnswer: 'mc-correct-answer',
  sortableItem: 'wpProQuiz_sortStringItem ui-sortable-handle',
  reShowButton: 'wpProQuiz_button_reShowQuestion'
};

const SELECTORS = {
  calendar: `.${CSS_CLASSES.calendar}`,
  questionText: '.wpProQuiz_question_text',
  matrixSortString: '.wpProQuiz_matrixSortString', // Use class selector directly
  questionList: `.${CSS_CLASSES.questionList}`,
  matrixSortText: `.${CSS_CLASSES.matrixSortText}`,
  matrixSortCriterion: `.${CSS_CLASSES.matrixSortCriterion}.ui-sortable`,
  questionListItem: `.${CSS_CLASSES.questionListItem}`,
  externalEvents: `.${CSS_CLASSES.externalEvents}`,
  externalEventsBackup: `.${CSS_CLASSES.externalEventsBackup}`,
  externalEventsContainer: `.${CSS_CLASSES.externalEventsContainer}`,
  fcEvent: `.${CSS_CLASSES.fcEvent}`,
  answerMessage: `.${CSS_CLASSES.answerMessage}`,
  listItem: `.${CSS_CLASSES.listItem}`
};

// Utility Functions
const utils = {
  /**
   * Extract day and time information from a date
   * @param {Date} date
   * @returns {Object} Object containing day, hours, and minutes
   */
  extractDayAndTime(date) {
    return {
      day: date.getDay(),
      hours: date.getHours(),
      minutes: date.getMinutes()
    };
  },

  /**
   * Remove time notation from event title
   * @param {string} inputString
   * @returns {string} Title without time
   */
  removeTimeFromString(inputString) {
    const regex = /\s*\(\d{2}:\d{2}\)/;
    return inputString.replace(regex, '').trim();
  },

  /**
   * Parse datetime range string into start and end dates
   * @param {string} timeRange
   * @returns {Object} Object with start and end Date objects
   */
  parseDateTimeRange(timeRange) {
    const [startString, endString] = timeRange.split(' to ');
    return {
      start: new Date(startString.replace(' ', 'T')),
      end: new Date(endString.replace(' ', 'T'))
    };
  },

  /**
   * Get Monday of the current week shown in calendar
   * @param {Calendar} calendar
   * @returns {Date} Monday of the current week
   */
  getCalendarWeekMonday(calendar) {
    const calendarDate = calendar.getDate();
    const calendarWeekMonday = new Date(calendarDate);
    const dayOfWeek = calendarDate.getDay();
    const daysToMonday = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
    calendarWeekMonday.setDate(calendarDate.getDate() + daysToMonday);
    return calendarWeekMonday;
  },

  /**
   * Map weekend days to Friday for business calendar
   * @param {number} dayOfWeek
   * @returns {number} Days from Monday
   */
  mapToBusinessDay(dayOfWeek) {
    if (dayOfWeek === 0 || dayOfWeek === 6) { // Sunday or Saturday -> Friday
      return 4;
    }
    return dayOfWeek - 1; // Monday=0, Tuesday=1, etc.
  }
};

/**
 * Check if event time matches the specified time range
 * @param {Date} eventStart
 * @param {Date} eventEnd
 * @param {string} timeRange
 * @returns {boolean}
 */
function isEventTimeMatching(eventStart, eventEnd, timeRange) {
  const { start: desiredStart, end: desiredEnd } = utils.parseDateTimeRange(timeRange);
  
  const eventStartInfo = utils.extractDayAndTime(eventStart);
  const eventEndInfo = utils.extractDayAndTime(eventEnd);
  const desiredStartInfo = utils.extractDayAndTime(desiredStart);
  const desiredEndInfo = utils.extractDayAndTime(desiredEnd);

  // Compare days and times
  const startMatch =
    eventStartInfo.day === desiredStartInfo.day &&
    eventStartInfo.hours === desiredStartInfo.hours &&
    eventStartInfo.minutes === desiredStartInfo.minutes;

  const endMatch =
    eventEndInfo.day === desiredEndInfo.day &&
    eventEndInfo.hours === desiredEndInfo.hours &&
    eventEndInfo.minutes === desiredEndInfo.minutes;

  // Handle overnight transition
  const overnightMatch =
    (eventStartInfo.day + 1) % 7 === desiredEndInfo.day &&
    eventEndInfo.hours === desiredEndInfo.hours &&
    eventEndInfo.minutes === desiredEndInfo.minutes;

  return startMatch && (endMatch || overnightMatch);
}

/**
 * Calendar Manager Class
 */
class CalendarQuizManager {
  constructor(questionElement) {
    this.question = questionElement;
    this.$question = jQuery(questionElement);
    this.calendar = null;
    this.calendarEl = null;
  }

  /**
   * Initialize the calendar
   */
  init() {
    if (jQuery('body').hasClass(CSS_CLASSES.quizCompleted)) {
      return;
    }

    this.setupUI();
    this.createCalendar();
    this.setupDraggable();
    this.updateEventsContainerHeight();
    this.attachEventListeners();
  }

  /**
   * Setup initial UI
   */
  setupUI() {
    this.$question.find(SELECTORS.questionText).addClass(CSS_CLASSES.questionTextCalendar);
    // Hide both the matrix sort string container and question list
    this.$question.find('.wpProQuiz_matrixSortString, .wpProQuiz_questionList').hide();
  }

  /**
   * Create and configure the calendar
   */
  createCalendar() {
    this.calendarEl = this.question.querySelector(SELECTORS.calendar);
    
    const config = {
      ...CALENDAR_CONFIG,
      plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
      editable: true,
      droppable: true,
      eventResizableFromStart: false,
      eventDurationEditable: false,
      events: JSON.parse(this.calendarEl.dataset.default_events || '[]'),
      eventDrop: () => this.handleEventChange(),
      eventResize: () => this.handleEventChange(),
      eventReceive: (info) => this.handleEventReceive(info),
      eventDragStop: (info) => this.handleEventDragStop(info),
      eventAllow: (dropInfo, draggedEvent) => {
        return !draggedEvent._def.extendedProps?.is_default;
      },
      dayHeaderContent: (arg) => arg.text.split(' ')[0],
      eventDidMount: (info) => {
        if (info.event.extendedProps.is_default) {
          info.el.classList.add(CSS_CLASSES.defaultEvent);
        }
      },
      eventStartEditable: true
    };

    this.calendar = new Calendar(this.calendarEl, config);
    this.calendar.render();
    
    // Store calendar instance for later access
    this.$question.data('calendar-instance', this.calendar);
  }

  /**
   * Setup draggable events
   */
  setupDraggable() {
    const externalEventsEl = this.calendarEl
      .closest(SELECTORS.questionText)
      ?.querySelector(SELECTORS.externalEvents);

    if (externalEventsEl) {
      this.initializeDraggableEvents(externalEventsEl);
    }
  }

  /**
   * Initialize draggable functionality for external events
   */
  initializeDraggableEvents(container) {
    new Draggable(container, {
      itemSelector: SELECTORS.fcEvent,
      eventData: (eventEl) => ({
        id: eventEl.getAttribute('data-id'),
        title: utils.removeTimeFromString(eventEl.innerText),
        duration: eventEl.getAttribute('data-duration')
      }),
      longPressDelay: 50,
      delay: 50,
      touchStartThreshold: 5
    });
  }

  /**
   * Handle event changes (drop/resize)
   */
  handleEventChange() {
    // Clear existing sorted items
    this.$question.find(SELECTORS.matrixSortCriterion).html('');

    const allEvents = this.calendar.getEvents();
    const self = this;

    allEvents.forEach(event => {
      this.$question.find(SELECTORS.matrixSortText).each(function() {
        const datetime = jQuery(this).html();
        const isMatch = isEventTimeMatching(event.start, event.end, datetime);

        if (isMatch) {
          const pos = jQuery(this).parents(SELECTORS.questionListItem).data('pos');
          const targetElement = self.$question
            .find(`${SELECTORS.questionListItem}[data-pos="${pos}"] ${SELECTORS.matrixSortCriterion}`);
          
          targetElement.html(
            `<li class="${CSS_CLASSES.sortableItem}" data-pos="${event.id}">${event.title}</li>`
          );
        }
      });
    });
  }

  /**
   * Handle event receive (from external drag)
   */
  handleEventReceive(info) {
    this.handleEventChange();

    // Remove the dragged element
    const eventEl = info.draggedEl;
    if (eventEl.parentNode) {
      eventEl.parentNode.removeChild(eventEl);
    }
  }

  /**
   * Handle event drag stop
   */
  handleEventDragStop(info) {
    const calendarBounds = this.calendarEl.getBoundingClientRect();
    const { clientX: mouseX, clientY: mouseY } = info.jsEvent;

    // Check if dropped outside calendar
    if (mouseX < calendarBounds.left || 
        mouseX > calendarBounds.right || 
        mouseY < calendarBounds.top || 
        mouseY > calendarBounds.bottom) {
      
      const event = this.calendar.getEventById(info.event.id);
      if (event && !event.extendedProps?.is_default) {
        this.restoreExternalEvent(event);
        event.remove();
        this.handleEventChange();
      }
    }
  }

  /**
   * Restore event to external events container
   */
  restoreExternalEvent(event) {
    const $backup = this.$question.find(`${SELECTORS.externalEventsBackup} ${SELECTORS.fcEvent}[data-id="${event.id}"]`);
    
    // Only proceed if backup exists
    if ($backup.length) {
      const $container = this.$question.find(SELECTORS.externalEvents);
      $backup.clone().appendTo($container);
      
      // Sort events by ID
      const $events = $container.find(SELECTORS.fcEvent);
      $events.sort((a, b) => parseInt(jQuery(a).data('id')) - parseInt(jQuery(b).data('id')));
      $container.html($events);
    }
  }

  /**
   * Update events container height to match calendar
   */
  updateEventsContainerHeight() {
    const eventsContainer = document.querySelector(SELECTORS.externalEventsContainer);
    
    if (this.calendarEl && eventsContainer) {
      const calendarHeight = this.calendarEl.offsetHeight;
      eventsContainer.style.maxHeight = `${calendarHeight}px`;
    }
  }

  /**
   * Attach event listeners
   */
  attachEventListeners() {
    // Update container height on window resize
    window.addEventListener('resize', () => this.updateEventsContainerHeight());
    
    // Note: The incorrect answer feedback handler is attached globally
    // It will find the calendar instance from the question element
  }

  /**
   * Prepare events for feedback calendar
   */
  prepareFeedbackEvents(calendar, $listItem, questionEl) {
    const feedbackEvents = [];
    
    // Add default events only
    calendar.getEvents().forEach(event => {
      if (event.extendedProps?.is_default) {
        feedbackEvents.push({
          id: event.id,
          title: event.title,
          start: event.start,
          end: event.end,
          extendedProps: event.extendedProps
        });
      }
    });

    // Add correct/incorrect answer events
    $listItem.find(SELECTORS.matrixSortText).each((index, element) => {
      const processedEvent = this.processAnswerEvent(element, calendar, questionEl);
      if (processedEvent) {
        feedbackEvents.push(processedEvent);
      }
    });

    return feedbackEvents;
  }

  /**
   * Process individual answer event for feedback
   */
  processAnswerEvent(element, calendar, questionEl) {
    const $element = jQuery(element);
    const datetime = $element.html();
    
    if (!datetime || !datetime.includes(' to ')) return null;

    const $currentItem = $element.parents(SELECTORS.questionListItem);
    const correct = $currentItem.hasClass(CSS_CLASSES.answerCorrect);
    const currentPos = $currentItem.data('pos');

    // Parse datetime and map to calendar week
    const eventDates = this.mapEventToCalendarWeek(datetime, calendar);
    if (!eventDates) return null;

    // Get backup event details
    const $backup = jQuery(questionEl)
      .find(`${SELECTORS.externalEventsBackup} ${SELECTORS.fcEvent}[data-pos="${currentPos}"]`);
    
    if (!$backup.length) return null;

    const eventId = $backup.data('id');
    const backupTitle = $backup.find(`.${CSS_CLASSES.fcEventMain}`).text();
    const eventTitle = backupTitle.replace(/\s*\(\d{2}:\d{2}\)/, '').trim();

    return {
      id: `${eventId}_processed`,
      title: eventTitle,
      start: eventDates.start,
      end: eventDates.end,
      backgroundColor: correct ? 'green' : 'red',
      extendedProps: {
        is_correct_answer: correct,
        original_pos: currentPos
      }
    };
  }

  /**
   * Map event datetime to calendar week
   */
  mapEventToCalendarWeek(datetime, calendar) {
    try {
      const { start: originalStart, end: originalEnd } = utils.parseDateTimeRange(datetime);
      const calendarWeekMonday = utils.getCalendarWeekMonday(calendar);
      
      // Extract time and day info
      const originalHours = originalStart.getHours();
      const originalMinutes = originalStart.getMinutes();
      const targetDayOfWeek = originalStart.getDay();
      
      // Map to business days
      const daysFromMonday = utils.mapToBusinessDay(targetDayOfWeek);
      
      // Create mapped dates
      const startDate = new Date(calendarWeekMonday);
      startDate.setDate(calendarWeekMonday.getDate() + daysFromMonday);
      startDate.setHours(originalHours, originalMinutes, 0, 0);
      
      // Calculate duration
      const duration = originalEnd.getTime() - originalStart.getTime() || 3600000; // Default 1 hour
      const endDate = new Date(startDate.getTime() + duration);
      
      return { start: startDate, end: endDate };
    } catch (e) {
      console.error('Error mapping event to calendar week:', e);
      return null;
    }
  }

  /**
   * Create feedback calendar
   */
  createFeedbackCalendar($messageBox, feedbackEvents) {
    const $feedbackCalendarEl = jQuery(`<div class="${CSS_CLASSES.calendar}"></div>`);
    $feedbackCalendarEl.css('min-width', '600px');
    $messageBox.append($feedbackCalendarEl);

    const feedbackConfig = {
      ...CALENDAR_CONFIG,
      plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
      editable: false,
      events: feedbackEvents,
      dayHeaderContent: (arg) => arg.text.split(' ')[0],
      eventDidMount: (info) => {
        if (info.event.extendedProps.is_default) {
          info.el.classList.add(CSS_CLASSES.defaultEvent);
        }
        if (info.event.extendedProps.is_correct_answer) {
          info.el.classList.add(CSS_CLASSES.correctAnswer);
        }
      }
    };

    const feedbackCalendar = new Calendar($feedbackCalendarEl[0], feedbackConfig);
    feedbackCalendar.render();

    // Fix sizing issues
    setTimeout(() => {
      $feedbackCalendarEl.show();
      feedbackCalendar.updateSize();
      setTimeout(() => feedbackCalendar.updateSize(), 200);
    }, 50);
  }
}

/**
 * Initialize function for individual calendar questions
 * Note: 'this' context is the DOM element when called from jQuery events
 */
function initCalendar() {
  // Skip if quiz is already completed
  if (jQuery('body').hasClass(CSS_CLASSES.quizCompleted)) {
    return;
  }
  
  const manager = new CalendarQuizManager(this);
  manager.init();
}

/**
 * Document ready initialization
 */
jQuery(document).ready(function($) {
  // Initialize existing calendars
  $(SELECTORS.calendar).each(function() {
    const $listItem = $(this).parents(`.${CSS_CLASSES.listItem}`);
    // Bind initCalendar with the listItem as context
    $listItem.on('mc_question_ready', function() {
      initCalendar.call(this);
    });
  });

  // Global event handler for incorrect answer feedback
  $(document).on(
    'learndash-quiz-answer-response-contentchanged',
    `.${CSS_CLASSES.incorrectAnswer}`,
    function(event) {
      const $incorrectEl = $(this);
      const $messageBox = $incorrectEl.find(`.${CSS_CLASSES.answerMessage}`);
      const $listItem = $incorrectEl.closest(`.${CSS_CLASSES.listItem}`);
      const questionEl = $listItem[0];

      const calendar = $(questionEl).data('calendar-instance');
      if (!calendar) return;

      // Create a temporary manager instance to handle feedback
      const tempManager = new CalendarQuizManager(questionEl);
      tempManager.calendar = calendar; // Use the existing calendar instance
      
      const feedbackEvents = tempManager.prepareFeedbackEvents(calendar, $listItem, questionEl);
      tempManager.createFeedbackCalendar($messageBox, feedbackEvents);
    }
  );

  // Handle dynamic calendar initialization
  $(document).on('mc_answer_ready_organization_calendar', (event, args) => {
    const $container = $(`.${args.htmlClass} .wpProQuiz_question`);
    $container.html(`<div class="${CSS_CLASSES.calendar}"></div>`);

    const calendarEl = document.querySelector(`.${args.htmlClass} .wpProQuiz_question ${SELECTORS.calendar}`);
    
    if (!calendarEl) return;

    const config = {
      ...CALENDAR_CONFIG,
      plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
      editable: true,
      droppable: true,
      eventResizableFromStart: false,
      eventDurationEditable: false,
      events: args.data.events,
      eventDidMount: (info) => {
        if (info.event._def.extendedProps.is_default) {
          info.el.classList.add(CSS_CLASSES.defaultEvent);
        }
      },
      dayHeaderContent: (arg) => arg.text.split(' ')[0]
    };

    const calendar = new Calendar(calendarEl, config);
    calendar.render();

    $(`.${CSS_CLASSES.reShowButton}`).on('click', () => calendar.render());
  });
});