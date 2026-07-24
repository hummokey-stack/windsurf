import { defineMiddleware } from 'astro:middleware';
import { COOKIE_NAME, verifyAdminToken, type AdminSession } from './lib/auth';

declare global {
  namespace App {
    interface Locals {
      admin?: AdminSession;
    }
  }
}

export const onRequest = defineMiddleware(async (context, next) => {
  const url = new URL(context.request.url);

  // Guard for /admin routes
  if (url.pathname.startsWith('/admin') && url.pathname !== '/admin/login' && url.pathname !== '/admin/login/') {
    const sessionCookie = context.cookies.get(COOKIE_NAME)?.value;

    if (!sessionCookie) {
      return context.redirect(`/admin/login?redirect=${encodeURIComponent(url.pathname)}`);
    }

    const session = await verifyAdminToken(sessionCookie);
    if (!session) {
      context.cookies.delete(COOKIE_NAME, { path: '/' });
      return context.redirect(`/admin/login?redirect=${encodeURIComponent(url.pathname)}`);
    }

    context.locals.admin = session;
  }

  return next();
});
