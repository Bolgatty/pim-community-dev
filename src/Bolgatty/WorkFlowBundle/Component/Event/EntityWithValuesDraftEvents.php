<?php
namespace Bolgatty\WorkFlowBundle\Component\Event;

/**
 * EntityWithValuesDraftEvents events
 *
 * @author Firoj Ahmad <firojahmad07@gmail.com>
 */
class EntityWithValuesDraftEvents
{
    /**
     * This event is dispatched before draft is applied on an entity with values
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_APPLY = 'bolgatty_workflow.draft.pre_apply';

    /**
     * This event is dispatched after draft is applied on an entity with values
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_APPLY = 'bolgatty_workflow.draft.post_apply';

    /**
     * This event is dispatched before draft is approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_APPROVE = 'bolgatty_workflow.draft.pre_approve';

    /**
     * This event is dispatched after draft is approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_APPROVE = 'bolgatty_workflow.draft.post_approve';

    /**
     * This event is dispatched before draft is refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_REFUSE = 'bolgatty_workflow.draft.pre_refuse';

    /**
     * This event is dispatched after draft is refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_REFUSE = 'bolgatty_workflow.draft.post_refuse';

    /**
     * This event is dispatched before draft is partially approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_PARTIAL_APPROVE = 'bolgatty_workflow.draft.pre_partial_approve';

    /**
     * This event is dispatched after draft is partially approved
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_PARTIAL_APPROVE = 'bolgatty_workflow.draft.post_partial_approve';

    /**
     * This event is dispatched before draft is partially refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_PARTIAL_REFUSE = 'bolgatty_workflow.draft.pre_partial_refuse';

    /**
     * This event is dispatched after draft is partially refused
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_PARTIAL_REFUSE = 'bolgatty_workflow.draft.post_partial_refuse';

    /**
     * This event is dispatched before draft is removed
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_REMOVE = 'bolgatty_workflow.draft.pre_remove';

    /**
     * This event is dispatched after draft is removed
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_REMOVE = 'bolgatty_workflow.draft.post_remove';

    /**
     * This event is dispatched before draft is marked as ready
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const PRE_READY = 'bolgatty_workflow.draft.pre_ready';

    /**
     * This event is dispatched after draft is marked as ready
     *
     * The event listener receives a Symfony\Component\EventDispatcher\GenericEvent instance
     */
    const POST_READY = 'bolgatty_workflow.draft.post_ready';
}
